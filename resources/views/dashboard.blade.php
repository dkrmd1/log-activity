@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Log Isu & Permintaan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
@endpush

@section('content')

    {{-- Filter Bulan --}}
    <form method="GET" action="{{ route('dashboard') }}" class="row g-3 mb-4">
        <div class="col-auto">
            <input type="text" name="bulan" value="{{ $bulan }}" class="form-control"
                   placeholder='Filter bulan, contoh: "September 2026"'>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" type="submit">
                <i class="ti tabler-filter me-1"></i> Filter
            </button>
        </div>
        @if($bulan)
            <div class="col-auto">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        @endif
    </form>

    {{-- Ringkasan Isu --}}
    <h6 class="text-uppercase text-body-secondary mb-3">Log Isu</h6>
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Total Isu</p>
                        <h4 class="mb-0">{{ $issueCounts['total'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-primary p-3">
                        <i class="ti tabler-alert-circle icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Isu Open</p>
                        <h4 class="mb-0">{{ $issueCounts['open'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-danger p-3">
                        <i class="ti tabler-circle-dot icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Isu In Progress</p>
                        <h4 class="mb-0">{{ $issueCounts['in_progress'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-warning p-3">
                        <i class="ti tabler-loader-2 icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Isu Done</p>
                        <h4 class="mb-0">{{ $issueCounts['done'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-success p-3">
                        <i class="ti tabler-circle-check icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Permintaan --}}
    <h6 class="text-uppercase text-body-secondary mb-3">Log Permintaan</h6>
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Total Permintaan</p>
                        <h4 class="mb-0">{{ $requestCounts['total'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-primary p-3">
                        <i class="ti tabler-clipboard-list icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Permintaan Open</p>
                        <h4 class="mb-0">{{ $requestCounts['open'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-danger p-3">
                        <i class="ti tabler-circle-dot icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Permintaan In Progress</p>
                        <h4 class="mb-0">{{ $requestCounts['in_progress'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-warning p-3">
                        <i class="ti tabler-loader-2 icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Permintaan Done</p>
                        <h4 class="mb-0">{{ $requestCounts['done'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-success p-3">
                        <i class="ti tabler-circle-check icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Permohonan Reset --}}
    <h6 class="text-uppercase text-body-secondary mb-3">Permohonan Reset</h6>
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Total Permohonan</p>
                        <h4 class="mb-0">{{ $resetRequestCounts['total'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-primary p-3">
                        <i class="ti tabler-lock-open icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Permohonan Open</p>
                        <h4 class="mb-0">{{ $resetRequestCounts['open'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-danger p-3">
                        <i class="ti tabler-circle-dot icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Permohonan In Progress</p>
                        <h4 class="mb-0">{{ $resetRequestCounts['in_progress'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-warning p-3">
                        <i class="ti tabler-loader-2 icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1">Permohonan Done</p>
                        <h4 class="mb-0">{{ $resetRequestCounts['done'] }}</h4>
                    </div>
                    <span class="avatar-initial rounded bg-label-success p-3">
                        <i class="ti tabler-circle-check icon-md"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik Tren 6 Bulan Terakhir --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Tren Isu & Permintaan — 6 Bulan Terakhir</h6>
        </div>
        <div class="card-body">
            <div id="trendChart"></div>
        </div>
    </div>

    {{-- Isu berstatus Open / In Progress terbaru --}}
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Isu berstatus Open / In Progress terbaru</h6>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Isu</th>
                    <th>Pemohon</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($latestOpenIssues as $issue)
                    <tr>
                        <td>{{ $issue->occurred_at->format('d/m/Y') }}</td>
                        <td>{{ $issue->category }}</td>
                        <td>{{ $issue->title }}</td>
                        <td>{{ $issue->requester }}</td>
                        <td>
                            <span class="badge bg-label-{{ $issue->status->badgeColor() }}">
                                {{ $issue->status->value }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">
                            Tidak ada isu Open / In Progress saat ini.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script>
        (function () {
            const trendChartEl = document.querySelector('#trendChart');
            if (!trendChartEl || typeof ApexCharts === 'undefined') return;

            // Ambil warna teks & garis grid mengikuti tema aktif (light/dark)
            // supaya label sumbu & legend tetap terbaca, bukan hardcode hitam.
            function getChartTextColor() {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                return isDark ? '#adb0bb' : '#697a8d';
            }
            function getChartGridColor() {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                return isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(50, 71, 92, 0.12)';
            }

            const options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    foreColor: getChartTextColor(),
                },
                series: [
                    { name: 'Isu', data: @json($issueTrend) },
                    { name: 'Permintaan', data: @json($requestTrend) },
                    { name: 'Permohonan Reset', data: @json($resetRequestTrend) },
                ],
                xaxis: {
                    categories: @json($trendLabels),
                    labels: { style: { colors: getChartTextColor() } },
                    axisBorder: { color: getChartGridColor() },
                    axisTicks: { color: getChartGridColor() },
                },
                yaxis: {
                    forceNiceScale: true,
                    labels: {
                        formatter: (val) => Math.round(val),
                        style: { colors: getChartTextColor() },
                    },
                },
                colors: ['#696CFF', '#03C3EC', '#FFAB00'],
                plotOptions: {
                    bar: { borderRadius: 6, columnWidth: '45%' },
                },
                dataLabels: { enabled: false },
                legend: {
                    position: 'top',
                    labels: { colors: getChartTextColor() },
                },
                grid: {
                    strokeDashArray: 4,
                    borderColor: getChartGridColor(),
                },
                tooltip: {
                    theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light',
                },
            };

            const trendChart = new ApexCharts(trendChartEl, options);
            trendChart.render();

            // Ikuti perubahan tema secara langsung saat toggle Light/Dark/System
            document.querySelectorAll('[data-bs-theme-value]').forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    setTimeout(() => {
                        trendChart.updateOptions({
                            chart: { foreColor: getChartTextColor() },
                            xaxis: {
                                labels: { style: { colors: getChartTextColor() } },
                                axisBorder: { color: getChartGridColor() },
                                axisTicks: { color: getChartGridColor() },
                            },
                            yaxis: { labels: { style: { colors: getChartTextColor() } } },
                            legend: { labels: { colors: getChartTextColor() } },
                            grid: { borderColor: getChartGridColor() },
                            tooltip: { theme: document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light' },
                        });
                    }, 50);
                });
            });
        })();
    </script>
@endpush