<!doctype html>
<html lang="id" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer">
<head>
    <script>
        // Terapkan tema tersimpan (light/dark/system) sebelum halaman digambar,
        // supaya tidak balik ke light saat pindah halaman dan tidak ada flash.
        (function () {
            var templateName = document.documentElement.getAttribute('data-template');
            var storedTheme = localStorage.getItem('templateCustomizer-' + templateName + '--Theme') || 'light';
            var theme = storedTheme;
            if (theme === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>@yield('title', 'Dashboard') | {{ \App\Models\AppSetting::current()->app_name }}</title>

    <link rel="icon" type="image/x-icon" href="{{ \App\Models\AppSetting::current()->favicon_url ?? asset('assets/img/favicon/favicon.ico') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />

    @stack('styles')

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        {{-- ================= SIDEBAR / MENU ================= --}}
        <aside id="layout-menu" class="layout-menu menu-vertical menu">
            @php($appSetting = \App\Models\AppSetting::current())
            <div class="app-brand demo">
                <a href="{{ route('dashboard') }}" class="app-brand-link">
                    <span class="app-brand-logo demo d-flex align-items-center justify-content-start" style="height:52px; max-width:190px; overflow:hidden;">
                        @if($appSetting->logo_url)
                            <img src="{{ $appSetting->logo_url }}" alt="Logo" style="max-height:100%; max-width:100%; width:auto; height:auto; object-fit:contain;">
                        @else
                            <i class="ti tabler-clipboard-text icon-lg text-primary"></i>
                        @endif
                    </span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                    <i class="icon-base ti tabler-circle-dot icon-md align-middle"></i>
                </a>
            </div>

            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-smart-home"></i>
                        <div>Dashboard</div>
                    </a>
                </li>

                <li class="menu-header small text-uppercase"><span class="menu-header-text">Log Activity</span></li>

                <li class="menu-item {{ request()->routeIs('issues.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('issues.index') ? route('issues.index') : '#' }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-alert-circle"></i>
                        <div>Log Isu</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('requests.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('requests.index') ? route('requests.index') : '#' }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-clipboard-list"></i>
                        <div>Log Permintaan</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('reset-requests.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('reset-requests.index') ? route('reset-requests.index') : '#' }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-lock-open"></i>
                        <div>Permohonan Reset</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('backup-logs.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('backup-logs.index') ? route('backup-logs.index') : '#' }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-database-export"></i>
                        <div>Log Backup</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('server-checks.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('server-checks.index') ? route('server-checks.index') : '#' }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-server"></i>
                        <div>Kontrol Server</div>
                    </a>
                </li>

                <li class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <a href="{{ Route::has('reports.index') ? route('reports.index') : '#' }}" class="menu-link">
                        <i class="menu-icon icon-base ti tabler-report-analytics"></i>
                        <div>Laporan Bulanan</div>
                        @if(auth()->user()?->isGroupHead() && ($pendingReports = \App\Models\MonthlyReport::where('status', 'submitted')->count()) > 0)
                            <span class="badge rounded-pill bg-danger ms-auto" title="Laporan menunggu persetujuan Anda">{{ $pendingReports }}</span>
                        @endif
                    </a>
                </li>

                @if(auth()->user()?->isAdmin())
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Administrasi</span></li>
                    <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-users"></i>
                            <div>Manajemen User</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('control-items.*') ? 'active' : '' }}">
                        <a href="{{ route('control-items.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-list-check"></i>
                            <div>Item Kontrol Server</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <a href="{{ route('categories.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-category"></i>
                            <div>Kelola Kategori</div>
                        </a>
                    </li>
                    <li class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a href="{{ route('settings.edit') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-settings"></i>
                            <div>Pengaturan Aplikasi</div>
                        </a>
                    </li>
                @endif
            </ul>
        </aside>
        {{-- ================= /SIDEBAR ================= --}}

        <div class="menu-mobile-toggler d-xl-none rounded-1">
            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
                <i class="ti tabler-menu icon-base"></i>
            </a>
        </div>

        <div class="layout-page">
            {{-- ================= NAVBAR ================= --}}
            <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                        <i class="icon-base ti tabler-menu-2 icon-md"></i>
                    </a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                    <ul class="navbar-nav flex-row align-items-center ms-auto">
                        {{-- Style Switcher: Light / Dark / System --}}
                        <li class="nav-item navbar-dropdown dropdown-style-switcher dropdown me-2 me-xl-1">
                            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" id="nav-theme" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-sun icon-md theme-icon-active"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-styles mt-3" aria-labelledby="nav-theme">
                                <li>
                                    <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light" aria-pressed="false">
                                        <span><i class="icon-base ti tabler-sun icon-md me-3" data-icon="sun"></i>Light</span>
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                                        <span><i class="icon-base ti tabler-moon-stars icon-md me-3" data-icon="moon-stars"></i>Dark</span>
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system" aria-pressed="false">
                                        <span><i class="icon-base ti tabler-device-desktop-analytics icon-md me-3" data-icon="device-desktop-analytics"></i>System</span>
                                    </button>
                                </li>
                            </ul>
                            <span id="nav-theme-text" class="d-none">Toggle theme</span>
                        </li>
                        {{-- Notifikasi (tanda tangan / persetujuan) --}}
                        @include('layouts.partials.notification-bell')

                        <li class="nav-item navbar-dropdown dropdown-user dropdown">
                            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    @if(auth()->user()?->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="avatar" class="rounded-circle" style="width:38px;height:38px;object-fit:cover;">
                                    @else
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end mt-3">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <h6 class="mb-0">{{ auth()->user()->name ?? '-' }}</h6>
                                        <small class="text-body-secondary">{{ auth()->user()->email ?? '-' }}</small>
                                    </a>
                                </li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="icon-base ti tabler-user me-2 icon-md"></i>
                                        <span class="align-middle">Profil Saya</span>
                                    </a>
                                </li>
                                <li><div class="dropdown-divider my-1"></div></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="icon-base ti tabler-logout me-2 icon-md"></i>
                                            <span class="align-middle">Logout</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            {{-- ================= /NAVBAR ================= --}}

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <h4 class="fw-bold mb-4">@yield('page-title', 'Dashboard')</h4>

                    @yield('content')
                </div>

                <footer class="content-footer footer bg-footer-theme">
                    <div class="container-xxl">
                        <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                            <div>
                                © {{ date('Y') }} Group Information Technology and General Affair – PT BJB Sekuritas Jawa Barat
                            </div>
                        </div>
                    </div>
                </footer>

                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

@stack('scripts')
</body>
</html>
