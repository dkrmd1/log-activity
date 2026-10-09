<!doctype html>
<html lang="id" class="layout-wide" dir="ltr" data-skin="default" data-bs-theme="light" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Login | {{ \App\Models\AppSetting::current()->app_name }}</title>

    <link rel="icon" type="image/x-icon" href="{{ \App\Models\AppSetting::current()->favicon_url ?? asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>
<body>
<div class="authentication-wrapper authentication-cover">
    @php($appSetting = \App\Models\AppSetting::current())
    <a href="{{ route('login') }}" class="app-brand auth-cover-brand">
        <span class="app-brand-logo demo d-flex align-items-center justify-content-start" style="height:72px; max-width:260px; overflow:hidden;">
            @if($appSetting->logo_url)
                <img src="{{ $appSetting->logo_url }}" alt="Logo" style="max-height:100%; max-width:100%; width:auto; height:auto; object-fit:contain;">
            @else
                <i class="ti tabler-clipboard-text icon-lg text-primary"></i>
            @endif
        </span>
    </a>

    <div class="authentication-inner row m-0">
        <div class="d-none d-xl-flex col-xl-8 p-0">
            <div class="auth-cover-bg d-flex justify-content-center align-items-center">
                <img src="{{ asset('assets/img/illustrations/auth-login-illustration-light.png') }}" alt="login" class="my-5 auth-illustration" />
                <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}" alt="bg" class="platform-bg" />
            </div>
        </div>

        <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
            <div class="w-px-400 mx-auto mt-12 pt-5">
                <h4 class="mb-1">{{ $appSetting->app_name }} 👋</h4>
                <p class="mb-6">Group Information Technology and General Affair – PT BJB Sekuritas</p>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form id="formAuthentication" class="mb-6" action="{{ route('login.attempt') }}" method="POST">
                    @csrf
                    <div class="mb-6 form-control-validation">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autofocus />
                    </div>
                    <div class="mb-6 form-password-toggle form-control-validation">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group input-group-merge">
                            <input
                                type="password"
                                id="password"
                                class="form-control"
                                name="password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                            <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
                        </div>
                    </div>
                    <div class="my-8">
                        <div class="form-check mb-0 ms-2">
                            <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                            <label class="form-check-label" for="remember-me"> Ingat saya </label>
                        </div>
                    </div>
                    <button class="btn btn-primary d-grid w-100" type="submit">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
