<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    protected $fillable = [
        'jenis',
        'backup_date',
        'backup_time',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'backup_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Contoh hasil: "01 September 2026" (selalu Bahasa Indonesia).
     */
    public function getDateLabelAttribute(): string
    {
        return $this->backup_date
            ? $this->backup_date->copy()->locale('id')->translatedFormat('d F Y')
            : '-';
    }

    /**
     * Contoh hasil: "17.00"
     */
    public function getTimeLabelAttribute(): string
    {
        return $this->backup_time
            ? str_replace(':', '.', substr($this->backup_time, 0, 5))
            : '-';
    }

    /**
     * Contoh hasil: "17:00" (untuk isi input type="time").
     */
    public function getTimeInputAttribute(): ?string
    {
        return $this->backup_time ? substr($this->backup_time, 0, 5) : null;
    }

    public function scopeJenis(Builder $query, ?string $jenis): Builder
    {
        return $jenis ? $query->where('jenis', $jenis) : $query;
    }

    public function scopeBulan(Builder $query, ?string $bulanTahun): Builder
    {
        if (! $bulanTahun) {
            return $query;
        }

        $timestamp = strtotime('1 ' . $bulanTahun);

        if ($timestamp === false) {
            return $query;
        }

        return $query
            ->whereMonth('backup_date', date('n', $timestamp))
            ->whereYear('backup_date', date('Y', $timestamp));
    }

    public function scopeCari(Builder $query, ?string $cari): Builder
    {
        return $cari
            ? $query->where(function ($q) use ($cari) {
                $q->where('jenis', 'like', "%{$cari}%")
                    ->orWhere('notes', 'like', "%{$cari}%");
            })
            : $query;
    }
}
