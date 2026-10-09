<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan - {{ $periodLabel }}</title>
    <style>
        /* Margin atas besar supaya header (fixed) turun ke bawah, tidak mepet tepi kertas. */
        @page {
            margin: 142pt 72pt 62pt 72pt;
            counter-increment: page;
        }
        body { font-family: "Helvetica", sans-serif; font-size: 10pt; color: #000000; counter-reset: page 1; }

        /* ---------- HEADER ---------- */
        header { position: fixed; top: -104pt; left: 0; right: 0; height: 74pt; }
        table.doc-header { width: 100%; border-collapse: collapse; border: 1pt solid #000; }
        table.doc-header td { border: 1pt solid #000; padding: 0 6pt; height: 24pt; vertical-align: middle; font-size: 10pt; }
        table.doc-header td.logo-cell { width: 24%; text-align: center; padding: 3pt 6pt; }
        table.doc-header td.logo-cell img { max-height: 56pt; max-width: 100%; }
        table.doc-header td.title-cell { width: 30%; text-align: center; font-size: 10.5pt; }
        table.doc-header td.meta-cell { width: 46%; }
        .page-num:before { content: counter(page); }

        footer { position: fixed; bottom: -40pt; left: 0; right: 0; height: 20pt; font-size: 7pt; color: #666; text-align: right; }

        /* ---------- WATERMARK (diagonal, besar, abu-abu muda) ---------- */
        .watermark {
            position: fixed;
            top: 250pt;
            left: 12pt;
            width: 100%;
            text-align: center;
            font-size: 74pt;
            color: #DFDFDF;
            transform: rotate(-42deg);
            transform-origin: 50% 50%;
            z-index: -1;
        }

        /* ---------- TEKS ---------- */
        h1.page-title { font-size: 12pt; text-align: center; margin: 0 0 16pt 0; }
        h2.subsection-title { font-size: 10pt; margin: 8pt 0 3pt 0; }
        .section-body { margin-left: 36pt; }
        p { line-height: 1.5; text-align: justify; margin: 0 0 8pt 0; }
        table.sec-head { border-collapse: collapse; margin: 0 0 6pt 0; }
        table.sec-head td { padding: 0; font-size: 10.5pt; font-weight: bold; vertical-align: top; }
        table.sec-head td.sn { width: 22pt; }

        /* ---------- KONTROL DOKUMEN ---------- */
        table.doc-control { width: 100%; border-collapse: collapse; margin-top: 8pt; }
        table.doc-control th, table.doc-control td { border: 1pt solid #000; padding: 6pt 6pt; font-size: 10pt; text-align: center; }
        table.doc-control th { font-weight: bold; font-size: 9.5pt; }

        /* ---------- DAFTAR ISI (titik-titik + nomor halaman rata kanan) ---------- */
        table.toc { width: 100%; border-collapse: collapse; margin: 0 0 7pt 0; }
        table.toc td { padding: 0; font-size: 10pt; line-height: 12pt; vertical-align: bottom; white-space: nowrap; }
        table.toc td.t { padding-right: 3pt; }
        table.toc td.num { width: 22pt; }
        table.toc td.dots { width: 100%; background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAwCAYAAAAlzZsxAAAAMklEQVR4nGNgGAWjYBSMglEw4IARif0fhzgKYMKiGBsfQwPRgGwN6G7G6YdRMAqGDgAAgHsDCuMYCk4AAAAASUVORK5CYII=); background-repeat: repeat-x; background-position: right bottom; background-size: 2.88pt 12pt; }
        table.toc td.pg { text-align: right; padding-left: 2pt; }

        /* ---------- TABEL RINGKASAN ---------- */
        table.ringkasan { width: 60%; border-collapse: collapse; margin: 6pt auto 0 auto; }
        table.ringkasan th, table.ringkasan td { border: 1pt solid #000; padding: 6pt 8pt; font-size: 10pt; text-align: center; }
        table.ringkasan th { background-color: #8EAADB; font-weight: bold; }

        /* ---------- TABEL DATA ---------- */
        table.data { width: 100%; border-collapse: collapse; margin-top: 6pt; }
        table.data thead th {
            background-color: #5B9BD5;
            border: 1pt solid #000;
            font-size: 9pt;
            padding: 6pt 4pt;
            text-align: center;
            vertical-align: middle;
        }
        table.data tbody td {
            border: 1pt solid #000;
            padding: 5pt 4pt;
            vertical-align: middle;
            font-size: 8.5pt;
            white-space: pre-line;
        }
        table.data td.c { text-align: center; }
        .empty-state { text-align: center; color: #666; padding: 20pt 0; }

        /* ---------- PENGESAHAN (tanda tangan) ---------- */
        table.sign { width: 100%; border-collapse: collapse; margin-top: 22pt; }
        table.sign th, table.sign td { border: 1pt solid #000; padding: 5pt 6pt; font-size: 10pt; text-align: center; vertical-align: middle; }
        table.sign th { font-weight: bold; font-size: 9.5pt; }
        table.sign td.sig-cell { height: 78pt; }
        table.sign td.sig-cell img { max-height: 66pt; max-width: 90%; }
        table.sign .sig-name { font-weight: bold; text-decoration: underline; }
        table.sign .sig-pos { font-size: 8.5pt; }
        table.sign .sig-wait { color: #777; font-size: 8.5pt; }
    </style>
</head>
<body>

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
        <tr><td class="meta-cell">Halaman <span class="page-num"></span>@if(!empty($totalPages)) dari {{ $totalPages }}@endif</td></tr>
    </table>
</header>

<footer>
    Dokumen ini digenerate otomatis oleh {{ $appSetting->app_name }} pada {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB
</footer>

<div class="watermark">{{ !empty($isFinal) ? 'CONFIDENTIAL' : 'DRAFT' }}</div>

{{-- ============================================================ --}}
{{-- KONTROL DOKUMEN --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <h1 class="page-title">Kontrol Dokumen</h1>

    <p style="margin-top:6pt;"><strong>Informasi Dokumen:</strong></p>
    <table class="doc-control">
        <thead>
        <tr>
            <th style="width:19%;">Nomor Revisi</th>
            <th style="width:21%;">Tanggal</th>
            <th style="width:34%;">Uraian</th>
            <th style="width:26%;">Disusun Oleh</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $docVersion }}</td>
            <td>{{ now()->locale('id')->translatedFormat('d F Y') }}</td>
            <td>Laporan Bulanan {{ $periodLabel }}</td>
            <td>{{ $preparedBy }}</td>
        </tr>
        </tbody>
    </table>

    {{-- Pengesahan: Dibuat oleh (Staf) & Diketahui oleh (Group Head) --}}
    <table class="sign">
        <thead>
        <tr>
            <th style="width:50%;">Dibuat oleh,</th>
            <th style="width:50%;">Diketahui oleh,</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="sig-cell">
                @if(!empty($preparedSig)) <img src="{{ $preparedSig }}" alt="TTD"> @endif
            </td>
            <td class="sig-cell">
                @if(!empty($reviewedSig))
                    <img src="{{ $reviewedSig }}" alt="TTD">
                @elseif(!empty($approval) && $approval->status === 'submitted')
                    <span class="sig-wait">Menunggu persetujuan Group Head</span>
                @endif
            </td>
        </tr>
        <tr>
            <td>
                @if(!empty($approval))
                    <div class="sig-name">{{ $approval->prepared_name }}</div>
                    <div class="sig-pos">{{ $approval->prepared_position }}</div>
                    <div class="sig-pos">{{ $approval->prepared_at->locale('id')->translatedFormat('d F Y') }}</div>
                @else
                    <div class="sig-pos">Staf Information Technology and General Affair</div>
                @endif
            </td>
            <td>
                @if(!empty($approval) && $approval->status === 'approved')
                    <div class="sig-name">{{ $approval->reviewed_name }}</div>
                    <div class="sig-pos">{{ $approval->reviewed_position }}</div>
                    <div class="sig-pos">{{ $approval->reviewed_at->locale('id')->translatedFormat('d F Y') }}</div>
                @else
                    <div class="sig-pos">Group Head Information Technology and General Affair</div>
                @endif
            </td>
        </tr>
        </tbody>
    </table>
</div>

{{-- ============================================================ --}}
{{-- DAFTAR ISI --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <h1 class="page-title">Daftar Isi</h1>

    <table class="toc"><tr><td class="t">Kontrol Dokumen</td><td class="dots">&nbsp;</td><td class="pg">2</td></tr></table>
    <table class="toc"><tr><td class="num">1.</td><td class="t">Informasi Umum</td><td class="dots">&nbsp;</td><td class="pg">4</td></tr></table>
    <table class="toc"><tr><td class="t" style="padding-left:8pt;">1.1 Tujuan</td><td class="dots">&nbsp;</td><td class="pg">4</td></tr></table>
    <table class="toc"><tr><td class="t" style="padding-left:8pt;">1.2 Ruang Lingkup</td><td class="dots">&nbsp;</td><td class="pg">4</td></tr></table>
    <table class="toc"><tr><td class="num">2.</td><td class="t">Ringkasan Data Log Activity {{ $periodLabel }}</td><td class="dots">&nbsp;</td><td class="pg">5</td></tr></table>
    @foreach($sections as $i => $sec)
        <table class="toc"><tr><td class="num">{{ $sec['no'] }}.</td><td class="t">{{ $sec['title'] }}</td><td class="dots">&nbsp;</td><td class="pg">{{ $tocPages[$i] ?? '' }}</td></tr></table>
    @endforeach
</div>

{{-- ============================================================ --}}
{{-- INFORMASI UMUM --}}
{{-- ============================================================ --}}
<div style="page-break-after: always;">
    <table class="sec-head"><tr><td class="sn">1.</td><td>Informasi Umum</td></tr></table>

    <h2 class="subsection-title">1.1 Tujuan</h2>
    <div class="section-body">
        <p>
            Tujuan dari dokumen ini adalah untuk mendokumentasikan seluruh aktivitas penanganan isu, permintaan
            layanan, permohonan reset, dan pelaksanaan backup data yang tercatat dalam sistem {{ $appSetting->app_name }} pada periode
            {{ $periodLabel }}, sehingga proses dan penyelesaiannya tercatat dengan langkah baku serta dapat
            dipertanggungjawabkan.
        </p>
    </div>

    <h2 class="subsection-title">1.2 Ruang Lingkup</h2>
    <div class="section-body">
        <p>
            Ruang lingkup pada dokumen ini menyangkut data Log Isu, Log Permintaan, Permohonan Reset, Log Backup Data, dan Kontrol Server &amp; Jaringan yang
            tercatat pada periode {{ $periodLabel }}. Data yang ditampilkan meliputi ringkasan jumlah data per
            kategori beserta daftar rincian (Incident, Request, Reset &amp; Backup Data Summary) pada halaman-halaman
            berikutnya.
        </p>
    </div>
</div>

{{-- ============================================================ --}}
{{-- RINGKASAN DATA --}}
{{-- ============================================================ --}}
<div>
    <table class="sec-head"><tr><td class="sn">2.</td><td>Ringkasan Data Log Activity {{ $periodLabel }}</td></tr></table>

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
        <tr><td>Log Backup Data*</td><td>{{ $summary['backups'] }}</td></tr>
        <tr><td>Kontrol Server &amp; Jaringan (hari)*</td><td>{{ $summary['controls'] }}</td></tr>
        </tbody>
    </table>
    <p style="font-size:8.5pt; text-align:center; margin-top:4pt;">
        *Log Backup Data dan Kontrol Server &amp; Jaringan tidak termasuk dalam Total Keseluruhan karena tidak memiliki status penyelesaian.
    </p>

    <p style="margin-top:18pt;">Rincian berdasarkan status penyelesaian:</p>
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
{{-- SECTION LIST (Incident / Request / Reset) - nomor berurutan, yang kosong dilewati --}}
{{-- ============================================================ --}}
@foreach($sections as $sec)
<div style="page-break-before: always;">
    <table class="sec-head"><tr><td class="sn">{{ $sec['no'] }}.</td><td>{{ $sec['title'] }}</td></tr></table>

    @if(($sec['type'] ?? 'list') === 'backup')
    @php
        $bk = $sec['items'];
        $colW = round(80 / max(count($bk['jenis']), 1), 2);
    @endphp
    <table class="data">
        <thead>
        <tr>
            <th style="width:20%">Tanggal</th>
            @foreach($bk['jenis'] as $j)
                <th style="width:{{ $colW }}%; font-size:8pt;">{{ $j }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($bk['rows'] as $row)
            <tr>
                <td class="c">{{ $row['date_label'] }}</td>
                @foreach($bk['jenis'] as $j)
                    <td class="c">{{ ($row['cells'][$j] ?? '') !== '' ? $row['cells'][$j] : '-' }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td class="c"><strong>Jumlah</strong></td>
            @foreach($bk['jenis'] as $j)
                <td class="c"><strong>{{ $bk['totals'][$j] ?? 0 }}</strong></td>
            @endforeach
        </tr>
        </tbody>
    </table>
    <p style="font-size:8.5pt; margin-top:6pt;">
        Keterangan: angka pada tabel adalah <strong>jam backup dilakukan</strong>; <strong>-</strong> = tidak ada backup pada tanggal tersebut.
        Baris <strong>Jumlah</strong> = banyaknya backup per jenis selama {{ $periodLabel }}.
    </p>
    @elseif(($sec['type'] ?? 'list') === 'kontrol')
    <table class="data">
        <thead>
        <tr>
            <th rowspan="2" style="width:8%">No</th>
            <th rowspan="2" style="width:32%">Tanggal</th>
            <th colspan="3">Status Pemeriksaan (Jam)</th>
        </tr>
        <tr>
            @foreach(\App\Models\ServerCheckEntry::SLOTS as $label)
                <th style="width:20%">{{ str_replace(' ', '', $label) }}</th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($sec['items'] as $item)
            <tr>
                <td class="c">{{ $loop->iteration }}</td>
                <td class="c">{{ $item->date_label }}</td>
                @foreach(\App\Models\ServerCheckEntry::SLOTS as $n => $label)
                    @php
                        $st = $item->slotStatus($n);
                    @endphp
                    <td class="c" @if($st === 'NOK') style="font-weight:bold; color:#C00000;" @endif>{{ $st }}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    <p style="font-size:8.5pt; margin-top:6pt;">
        Keterangan status: <strong>OK</strong> = seluruh item diperiksa dan normal; <strong>NOK</strong> = ada item bermasalah;
        <strong>Sebagian</strong> = belum semua item terisi; <strong>-</strong> = belum diperiksa.
    </p>
    @else
    <table class="data">
        <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:13%">Tanggal Terjadi</th>
            <th style="width:16%">Kategori</th>
            <th style="width:20%">Topik / Kasus</th>
            <th style="width:34%">Penjelasan</th>
            <th style="width:12%">Status</th>
        </tr>
        </thead>
        <tbody>
        @forelse($sec['items'] as $item)
            <tr>
                <td class="c">{{ $loop->iteration }}</td>
                <td class="c">{{ \Illuminate\Support\Carbon::parse($item['tanggal'])->format('d/m/Y') }}</td>
                <td>{{ $item['kategori'] }}</td>
                <td>{{ $item['topik'] }}</td>
                <td>{{ $item['penjelasan'] ?: '-' }}</td>
                <td class="c">{{ $item['status'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-state">Tidak ada data pada periode ini.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    @endif
</div>
@endforeach

</body>
</html>