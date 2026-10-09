<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Models\Issue;
use App\Models\ResetRequest;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');

        $issueCounts = [
            'total' => Issue::bulan($bulan)->count(),
            'open' => Issue::bulan($bulan)->status(StatusEnum::Open->value)->count(),
            'in_progress' => Issue::bulan($bulan)->status(StatusEnum::InProgress->value)->count(),
            'done' => Issue::bulan($bulan)->status(StatusEnum::Done->value)->count(),
        ];

        $requestCounts = [
            'total' => ServiceRequest::bulan($bulan)->count(),
            'open' => ServiceRequest::bulan($bulan)->status(StatusEnum::Open->value)->count(),
            'in_progress' => ServiceRequest::bulan($bulan)->status(StatusEnum::InProgress->value)->count(),
            'done' => ServiceRequest::bulan($bulan)->status(StatusEnum::Done->value)->count(),
        ];

        $latestOpenIssues = Issue::whereIn('status', [StatusEnum::Open->value, StatusEnum::InProgress->value])
            ->orderByDesc('occurred_at')
            ->take(5)
            ->get();

        $resetRequestCounts = [
            'total' => ResetRequest::bulan($bulan)->count(),
            'open' => ResetRequest::bulan($bulan)->status(StatusEnum::Open->value)->count(),
            'in_progress' => ResetRequest::bulan($bulan)->status(StatusEnum::InProgress->value)->count(),
            'done' => ResetRequest::bulan($bulan)->status(StatusEnum::Done->value)->count(),
        ];

        // Data tren 6 bulan terakhir (termasuk bulan berjalan) untuk grafik.
        $months = collect(range(5, 0))->map(fn ($i) => now()->copy()->subMonths($i)->startOfMonth());

        $trendLabels = $months->map(fn ($m) => $m->translatedFormat('M Y'))->values();

        $issueTrend = $months->map(function ($m) {
            return Issue::whereYear('occurred_at', $m->year)
                ->whereMonth('occurred_at', $m->month)
                ->count();
        })->values();

        $requestTrend = $months->map(function ($m) {
            return ServiceRequest::whereYear('occurred_at', $m->year)
                ->whereMonth('occurred_at', $m->month)
                ->count();
        })->values();

        $resetRequestTrend = $months->map(function ($m) {
            return ResetRequest::whereYear('requested_at', $m->year)
                ->whereMonth('requested_at', $m->month)
                ->count();
        })->values();

        return view('dashboard', compact(
            'issueCounts',
            'requestCounts',
            'resetRequestCounts',
            'latestOpenIssues',
            'bulan',
            'trendLabels',
            'issueTrend',
            'requestTrend',
            'resetRequestTrend'
        ));
    }
}
