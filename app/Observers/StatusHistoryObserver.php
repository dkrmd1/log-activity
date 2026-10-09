<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

/**
 * Observer generik: dipasang ke model Issue & ServiceRequest.
 * Keduanya sama-sama punya kolom "status", jadi 1 observer ini cukup untuk berdua.
 */
class StatusHistoryObserver
{
    public function created(Model $model): void
    {
        $model->activityLogs()->create([
            'user_id' => auth()->id(),
            'old_status' => null,
            'new_status' => $this->statusValue($model),
            'created_at' => now(),
        ]);
    }

    public function updated(Model $model): void
    {
        if (! $model->wasChanged('status')) {
            return;
        }

        $model->activityLogs()->create([
            'user_id' => auth()->id(),
            'old_status' => $model->getOriginal('status')?->value ?? $model->getOriginal('status'),
            'new_status' => $this->statusValue($model),
            'created_at' => now(),
        ]);
    }

    protected function statusValue(Model $model): string
    {
        return $model->status?->value ?? (string) $model->status;
    }
}
