{{--
    Form "Kontrol Server & Jaringan" (LANDSCAPE).
    Template yang SAMA dipakai untuk PDF 1 hari dan untuk lampiran di Laporan Bulanan,
    jadi tampilannya selalu identik. 1 tanggal = 1 halaman (lanjut ke halaman berikut kalau item banyak).

    Variabel:
      $checks           Collection<ServerCheck> (entries sudah di-eager-load)
      $sigs             array user_id => data URI tanda tangan (kolom Paraf)
      $showPageNumbers  bool  - nomor halaman kecil di kanan bawah (hanya saat jadi lampiran laporan)
      $pageStart        int   - nomor halaman pertama lampiran di laporan
      $totalPages       int   - total halaman laporan
--}}
@php
    $slots = \App\Models\ServerCheckEntry::SLOTS;
    $sigs = $sigs ?? [];
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kontrol Server &amp; Jaringan</title>
    <style>
        /* Warna header: ubah di sini kalau berbeda dengan form asli */
        @page { margin: 26pt 28pt 34pt 28pt; counter-increment: page; }

        body {
            font-family: "Helvetica", sans-serif;
            font-size: 7pt;
            color: #000;
            @if(!empty($showPageNumbers) && !empty($pageStart)) counter-reset: page {{ $pageStart - 1 }}; @endif
        }

        table.top { width: 100%; border-collapse: collapse; margin-bottom: 4pt; }
        table.top td { border: none; padding: 0; vertical-align: bottom; }
        td.ttl { font-size: 15pt; font-weight: bold; color: #808080; }
        td.tgl { text-align: right; font-size: 8.5pt; }

        table.form { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.form th, table.form td { border: 0.5pt solid #000; padding: 1.4pt 3pt; vertical-align: middle; }
        table.form tr { page-break-inside: avoid; }

        table.form tr.sp th { border: none; padding: 0; height: 0; line-height: 0; font-size: 0; background: none; }
        th.red { background: #FF4B4B; color: #fff; font-weight: bold; font-size: 8pt; text-align: center; }
        th.yel { background: #FFFF00; color: #000; font-weight: bold; font-size: 7pt; text-align: center; }

        td.letter { text-align: center; font-size: 6.5pt; }
        td.sec-title { font-weight: bold; text-decoration: underline; font-size: 7.5pt; }
        td.num { text-align: center; font-size: 6pt; }
        td.item { text-align: left; }
        td.cond { text-align: center; }
        td.note { text-align: left; vertical-align: top; font-size: 6.5pt; line-height: 1.35; }
        td.paraf { text-align: center; }
        td.paraf .pf { height: 9pt; overflow: hidden; }
        td.paraf img { height: 9pt; }
        td.paraf .ini { font-size: 6pt; }

        footer { position: fixed; bottom: -22pt; left: 0; right: 0; text-align: right; font-size: 7pt; color: #666; }
        .page-num:before { content: counter(page); }
    </style>
</head>
<body>

@if(!empty($showPageNumbers) && !empty($pageStart))
    <footer>Halaman <span class="page-num"></span>@if(!empty($totalPages)) dari {{ $totalPages }}@endif</footer>
@endif

@php
    $pageNo = 0;
@endphp

@foreach($checks as $check)
    {{-- Halaman dibagi di ServerCheckLayout: 1 bagian (A, B, C, ...) tidak dipotong antar-halaman. --}}
    @foreach(\App\Support\ServerCheckLayout::pages($check->entries) as $page)
        <div @if($pageNo++ > 0) style="page-break-before: always;" @endif>

            <table class="top">
                <tr>
                    <td class="ttl">KONTROL SERVER &amp; JARINGAN</td>
                    <td class="tgl">Tanggal, {{ $check->date_label }}</td>
                </tr>
            </table>

            <table class="form">
                <thead>
                {{-- Baris pengatur lebar kolom (tinggi 0). dompdf mengabaikan <colgroup>, jadi lebar diatur lewat baris ini. --}}
                <tr class="sp">
                    <th style="width:3.5%"></th>
                    <th style="width:2%"></th>
                    <th style="width:27%"></th>
                    @foreach($slots as $n => $label)
                        <th style="width:4.2%"></th>
                        <th style="width:16.5%"></th>
                    @endforeach
                    <th style="width:5.4%"></th>
                </tr>
                <tr>
                    <th class="red" rowspan="2">No</th>
                    <th class="red" rowspan="2" colspan="2">Item</th>
                    @foreach($slots as $n => $label)
                        <th class="red" colspan="2">{{ $label }}</th>
                    @endforeach
                    <th class="red">&nbsp;</th>
                </tr>
                <tr>
                    @foreach($slots as $n => $label)
                        <th class="yel">Kondisi</th>
                        <th class="yel">Keterangan</th>
                    @endforeach
                    <th class="yel">Paraf</th>
                </tr>
                </thead>
                <tbody>
                @foreach($page as $block)
                    @php
                        $rows = $block['rows'];
                        $count = count($rows);
                        $first = $rows[0];
                    @endphp
                    <tr>
                        <td class="letter" rowspan="{{ $count + 1 }}">{{ $block['code'] }}</td>
                        <td class="sec-title" colspan="9">{{ $block['title'] }}</td>
                    </tr>
                    @foreach($rows as $ri => $e)
                        <tr>
                            <td class="num">{{ $e->item_no }}</td>
                            <td class="item">{{ $e->item_name }}</td>
                            @foreach($slots as $n => $label)
                                <td class="cond">{{ $e->{'c' . $n} }}</td>
                                @if($block['merge'])
                                    @if($ri === 0)
                                        <td class="note" rowspan="{{ $count }}">{!! $block['continued'] ? '' : nl2br(e($first->{'n' . $n})) !!}</td>
                                    @endif
                                @else
                                    <td class="note">{!! nl2br(e($e->{'n' . $n})) !!}</td>
                                @endif
                            @endforeach
                            <td class="paraf">
                                <div class="pf">
                                    @if($e->paraf_by && !empty($sigs[$e->paraf_by]))
                                        <img src="{{ $sigs[$e->paraf_by] }}" alt="">
                                    @elseif($e->paraf_by && $e->parafUser)
                                        <span class="ini">{{ $e->parafUser->name }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endforeach

</body>
</html>
