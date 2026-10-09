<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ServiceRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'occurred_at',
        'category',
        'title',
        'requester',
        'follow_up',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'occurred_at' => 'date',
        'completed_at' => 'date',
        'status' => StatusEnum::class,
    ];

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable')->latest('created_at');
    }

    public function getMonthLabelAttribute(): string
    {
        return $this->occurred_at
            ? $this->occurred_at->translatedFormat('F Y')
            : '-';
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
            ->whereMonth('occurred_at', date('n', $timestamp))
            ->whereYear('occurred_at', date('Y', $timestamp));
    }
}
