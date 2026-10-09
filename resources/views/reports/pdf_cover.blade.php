<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cover</title>
    <style>
        @page { margin: 0; }
        body { font-family: "Helvetica", sans-serif; font-size: 10pt; color: #000; margin: 0; padding: 0; }
        .cover-page { text-align: center; }
        .title-block { position: absolute; top: 100pt; left: 0; width: 100%; font-size: 15pt; font-weight: bold; line-height: 30pt; }
        .logo-block { position: absolute; top: 300pt; left: 0; width: 100%; height: 170pt; }
        .logo-block img { max-height: 160pt; max-width: 300pt; }
        .prepared-block { position: absolute; top: 585pt; left: 0; width: 100%; font-size: 12pt; font-weight: bold; line-height: 26pt; }
    </style>
</head>
<body>
<div class="cover-page">
    <div class="title-block">
        Laporan Bulanan<br>
        {{ $appSetting->app_name }}<br>
        {{ $periodLabel }}
    </div>

    @if($logoBase64)
        <div class="logo-block"><img src="{{ $logoBase64 }}" alt="Logo"></div>
    @endif

    <div class="prepared-block">
        Disiapkan oleh<br>
        Sistem {{ $appSetting->app_name }}
    </div>
</div>
</body>
</html>