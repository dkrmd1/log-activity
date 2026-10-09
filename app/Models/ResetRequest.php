<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ResetRequest extends Model
{
    protected $fillable = [
        'requested_at',
        'account_user_id',
        'name',
        'email',
        'types',
        'notes',
        'status',
    ];

    protected $casts = [
        'requested_at' => 'date',
        'types' => 'array',
        'status' => StatusEnum::class,
    ];

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest('created_at');
    }

    public function getMonthLabelAttribute(): string
    {
        return $this->requested_at
            ? $this->requested_at->translatedFormat('F Y')
            : '-';
    }

    /**
     * Contoh hasil: "Reset PIN, Unblock"
     */
    public function getTypesLabelAttribute(): string
    {
        return implode(', ', $this->types ?? []);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
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
            ->whereMonth('requested_at', date('n', $timestamp))
            ->whereYear('requested_at', date('Y', $timestamp));
    }
}
