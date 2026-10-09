<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyReport extends Model
{
    public const SUBMITTED = 'submitted';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'year', 'month', 'status', 'data_fingerprint',
        'prepared_by', 'prepared_name', 'prepared_position', 'prepared_signature', 'prepared_at',
        'reviewed_by', 'reviewed_name', 'reviewed_position', 'reviewed_signature', 'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'prepared_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function scopeForPeriod(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query->where('month', $bulan)->where('year', $tahun);
    }

    /**
     * Ringkasan data 1 bulan: jumlah baris + "sidik jari". Sidik jari berubah kalau ada data
     * ditambah, dihapus, atau diedit di bulan itu -> dipakai untuk mendeteksi laporan yang
     * sudah diajukan/disetujui tapi datanya berubah lagi sesudahnya.
     *
     * @return array{count:int, fingerprint:string}
     */
    public static function snapshotFor(int $bulan, int $tahun): array
    {
        $sources = [
            [Issue::class, 'occurred_at'],
            [ServiceRequest::class, 'occurred_at'],
            [ResetRequest::class, 'requested_at'],
            [BackupLog::class, 'backup_date'],
            [ServerCheck::class, 'check_date'],
        ];

        $parts = [];
        $total = 0;

        foreach ($sources as [$model, $column]) {
            $count = $model::whereYear($column, $tahun)->whereMonth($column, $bulan)->count();
            $latest = $model::whereYear($column, $tahun)->whereMonth($column, $bulan)->max('updated_at');

            $total += $count;
            $parts[] = [$count, (string) $latest];
        }

        return ['count' => $total, 'fingerprint' => md5(json_encode($parts))];
    }

    /**
     * Apakah tanda tangan di laporan ini masih berlaku untuk data bulan itu saat ini?
     */
    public function isCurrent(string $fingerprint): bool
    {
        return $this->data_fingerprint === $fingerprint;
    }
}
