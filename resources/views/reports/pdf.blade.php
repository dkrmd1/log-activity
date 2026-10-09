<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - {{ $periodLabel }}</title>
    <style>
        @page { margin: 100px 40px 50px 40px; }

        body { font-family: "Helvetica", sans-serif; font-size: 10px; color: #000000; }

        /* ================= COVER (halaman 1, tanpa header/footer) ================= */
        .cover-page {
            page-break-after: always;
            margin: -100px -40px -50px -40px; /* keluar dari margin @page, supaya benar-benar polos tanpa header/footer */
            padding: 90px 40px;
            text-align: center;
        }
        .cover-page .title-block { font-size: 14px; font-weight: bold; line-height: 1.9; margin-bottom: 90px; }
        .cover-page img.cover-logo { max-height: 130px; max-width: 340px; margin-bottom: 90px; }
        .cover-page .prepared-label { font-size: 12px; font-weight: bold; margin-bottom: 4px; }
        .cover-page .prepared-org { font-size: 12px; font-weight: bold; }

        /* ================= HEADER (halaman 2 dst) ================= */
        header { position: fixed; top: -100px; left: -40px; right: -40px; padding: 0 40px; height: 100px; }
        table.doc-header { width: 100%; border-collapse: collapse; border: 1px solid #000; }
        table.doc-header td { border: 1px solid #000; padding: 6px 10px; vertical-align: middle; font-size: 9.5px; }
        table.doc-header td.logo-cell { width: 16%; text-align: center; }
        table.doc-header td.logo-cell img { max-height: 46px; max-width: 100%; }
        table.doc-header td.title-cell { width: 40%; text-align: center; font-weight: bold; font-size: 11px; }
        table.doc-header td.meta-cell { width: 44%; }

        /* ================= FOOTER (halaman 2 dst) ================= */
        footer { position: fixed; bottom: -50px; left: -40px; right: -40px; padding: 0 40px; height: 30px; font-size: 8px; color: #666; text-align: right; }

        /* ================= WATERMARK CONFIDENTIAL ================= */
        .watermark {
            position: fixed;
            top: 320px;
            left: 120px;
            font-size: 70px;
            font-weight: bold;
            color: #000;
            opacity: 0.06;
            transform: rotate(35deg);
        }

        h1.section-title { font-size: 13px; margin: 0 0 14px 0; }
        h2.subsection-title { font-size: 11px; margin: 16px 0 6px 0; }
        p { line-height: 1.5; text-align: justify; margin: 0 0 10px 0; }

        table.doc-control { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.doc-control th, table.doc-control td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5px; text-align: left; }
        table.doc-control th { background-color: #DCE6F1; font-weight: bold; }

        .toc-line { display: block; margin-bottom: 7px; font-size: 10px; }
        .toc-dots { border-bottom: 1px dotted #666; }

        table.ringkasan { width: 60%; border-collapse: collapse; margin-top: 8px; }
        table.ringkasan th, table.ringkasan td { border: 1px solid #000; padding: 6px 10px; font-size: 9.5px; }
        table.ringkasan th { background-color: #DCE6F1; text-align: left; }
        table.ringkasan td { text-align: right; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data thead th {
            background-color: #DCE6F1;
            border: 1px solid #000;
            font-size: 9px;
            padding: 6px 5px;
            text-align: left;
        }
        table.data tbody td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            font-size: 8.8px;
            white-space: pre-line;
        }
        .empty-state { text-align: center; color: #666; padding: 20px 0; }
    </style>
</head>
<body>

{{-- ============================================================ --}}
{{-- HALAMAN 1: COVER — tanpa header/footer sama sekali --}}
{{-- ============================================================ --}}
<div class="cover-page">
    <div class="title-block">
        Laporan Bulanan<br>
        {{ $appSetting->app_name }}<br>
        {{ $periodLabel }}
    </div>

    @if($logoBase64)
        <div><img class="cover-logo" src="{{ $logoBase64 }}" alt="Logo"></div>
    @endif

    <div class="prepared-label">Disiapkan oleh</div>
    <div class="prepared-org">Sistem {{ $appSetting->app_name }}</div>
</div>

{{-- ============================================================ --}}
{{-- HEADER & FOOTER berulang untuk halaman 2 dst --}}
{{-- ============================================================ --}}
<header>
    <table class="doc-header">
        <tr>
            <td class="logo-cell" rowspan="3">
                @if($logoBase64)<img src="{{ $logoBase64 }}" alt="Logo">@endif
            </td>
            <td class="title-cell" rowspan="3">Laporan Bulanan</td>
            <td class="meta-cell">No. Dokumen: {{ $docNumber }}</td>
        </tr>
        <tr><td class="meta-cell">Versi: {{ $docVersion }}</td></tr>
        <tr><td class="meta-cell">Halaman <span class="dompdf-pagenum"></span></td></tr>
    </table>
</header>

<footer>
    Dokumen ini digenerate otomatis oleh {{ $appSetting->app_name }} pada {{ now()->translatedFormat('d F Y, H:i') }} WIB
</footer>

<div class="watermark">CONFIDENTIAL</div>

{{-- ============================================================ --}}
{{-- HALAMAN 2: KONTROL DOKUMEN --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <h1 class="section-title" style="text-align:center;">Kontrol Dokumen</h1>

    <p style="margin-top:20px;"><strong>Informasi Dokumen:</strong></p>
    <table class="doc-control">
        <thead>
        <tr>
            <th style="width:12%;">Nomor Revisi</th>
            <th style="width:18%;">Tanggal</th>
            <th style="width:45%;">Uraian</th>
            <th style="width:25%;">Disusun Oleh</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $docVersion }}</td>
            <td>{{ now()->translatedFormat('d F Y') }}</td>
            <td>Laporan Bulanan {{ $periodLabel }}</td>
            <td>{{ $preparedBy }}</td>
        </tr>
        </tbody>
    </table>
</div>

{{-- ============================================================ --}}
{{-- HALAMAN 3: DAFTAR ISI --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <h1 class="section-title" style="text-align:center;">Daftar Isi</h1>

    <div style="margin-top:20px;">
        <span class="toc-line">Kontrol Dokumen <span class="toc-dots">&nbsp;</span> 2</span>
        <span class="toc-line">1. Informasi Umum <span class="toc-dots">&nbsp;</span> 4</span>
        <span class="toc-line" style="margin-left:16px;">1.1 Tujuan <span class="toc-dots">&nbsp;</span> 4</span>
        <span class="toc-line" style="margin-left:16px;">1.2 Ruang Lingkup <span class="toc-dots">&nbsp;</span> 4</span>
        <span class="toc-line">2. Ringkasan Data Log Activity {{ $periodLabel }} <span class="toc-dots">&nbsp;</span> 5</span>
        <span class="toc-line">3. Incident, Request &amp; Reset List Summary {{ $periodLabel }} <span class="toc-dots">&nbsp;</span> 6</span>
    </div>
</div>

{{-- ============================================================ --}}
{{-- HALAMAN 4: INFORMASI UMUM --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <h1 class="section-title">1. Informasi Umum</h1>

    <h2 class="subsection-title">1.1 Tujuan</h2>
    <p>
        Tujuan dari dokumen ini adalah untuk mendokumentasikan seluruh aktivitas penanganan isu, permintaan
        layanan, dan permohonan reset yang tercatat dalam sistem {{ $appSetting->app_name }} pada periode
        {{ $periodLabel }}, sehingga proses dan penyelesaiannya tercatat dengan langkah baku serta dapat
        dipertanggungjawabkan.
    </p>

    <h2 class="subsection-title">1.2 Ruang Lingkup</h2>
    <p>
        Ruang lingkup pada dokumen ini menyangkut data Log Isu, Log Permintaan, dan Permohonan Reset yang
        tercatat pada periode {{ $periodLabel }}. Data yang ditampilkan meliputi ringkasan jumlah data per
        kategori beserta daftar rincian (Incident, Request &amp; Reset List Summary) pada halaman-halaman
        berikutnya.
    </p>
</div>

{{-- ============================================================ --}}
{{-- HALAMAN 5: RINGKASAN DATA --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <h1 class="section-title">2. Ringkasan Data Log Activity {{ $periodLabel }}</h1>

    <p>Jumlah data yang tercatat pada periode {{ $periodLabel }} sebagai berikut:</p>

    <table class="ringkasan">
        <thead>
        <tr><th>Sumber Data</th><th>Jumlah</th></tr>
        </thead>
        <tbody>
        <tr><td>Log Isu</td><td>{{ $summary['issues'] }}</td></tr>
        <tr><td>Log Permintaan</td><td>{{ $summary['requests'] }}</td></tr>
        <tr><td>Permohonan Reset</td><td>{{ $summary['reset_requests'] }}</td></tr>
        <tr><td><strong>Total Keseluruhan</strong></td><td><strong>{{ $summary['total'] }}</strong></td></tr>
        </tbody>
    </table>

    <p style="margin-top:20px;">Rincian berdasarkan status penyelesaian:</p>
    <table class="ringkasan">
        <thead>
        <tr><th>Status</th><th>Jumlah</th></tr>
        </thead>
        <tbody>
        <tr><td>Open</td><td>{{ $summary['open'] }}</td></tr>
        <tr><td>In Progress</td><td>{{ $summary['in_progress'] }}</td></tr>
        <tr><td>Done</td><td>{{ $summary['done'] }}</td></tr>
        <tr><td>Cancelled</td><td>{{ $summary['cancelled'] }}</td></tr>
        </tbody>
    </table>
</div>

{{-- ============================================================ --}}
{{-- HALAMAN 6+: SUMMARY TABLE --}}
{{-- ============================================================ --}}
<div>
    <h1 class="section-title">3. Incident, Request &amp; Reset List Summary {{ $periodLabel }}</h1>

    <table class="data">
        <thead>
        <tr>
            <th style="width:3%">No</th>
            <th style="width:8%">Tanggal Terjadi</th>
            <th style="width:11%">Sumber</th>
            <th style="width:12%">Kategori</th>
            <th style="width:16%">Topik / Kasus</th>
            <th style="width:38%">Penjelasan</th>
            <th style="width:8%">Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $item['sumber'] }}</td>
                <td>{{ $item['kategori'] }}</td>
                <td>{{ $item['topik'] }}</td>
                <td>{{ $item['penjelasan'] ?: '-' }}</td>
                <td>{{ $item['status'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="empty-state">Tidak ada data pada periode ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->get_font("helvetica", "normal");
        $pdf->page_text(495, 55, "{PAGE_NUM} dari {PAGE_COUNT}", $font, 9, array(0,0,0));
    }
</script>

</body>
</html>
