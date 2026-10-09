<?php

namespace App\Providers;

use App\Models\Issue;
use App\Models\ResetRequest;
use App\Models\ServiceRequest;
use App\Observers\StatusHistoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Issue::observe(StatusHistoryObserver::class);
        ServiceRequest::observe(StatusHistoryObserver::class);
        ResetRequest::observe(StatusHistoryObserver::class);
    }
}
