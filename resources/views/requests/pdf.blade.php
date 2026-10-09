<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log Permintaan</title>
    <style>
        @page { margin: 90px 28px 60px 28px; }

        body { font-family: "Helvetica", sans-serif; font-size: 10.5px; color: #2b2b40; }

        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 70px;
        }

        .header-bar {
            background-color: #696CFF;
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 6px;
        }
        .header-bar table { width: 100%; border: none; }
        .header-bar td { border: none; padding: 0; }
        .header-title { font-size: 16px; font-weight: bold; }
        .header-subtitle { font-size: 9.5px; color: #E7E7FF; }
        .header-meta { text-align: right; font-size: 9px; color: #ffffff; }

        .info-bar {
            margin-top: 8px;
            font-size: 9.5px;
            color: #5b5b73;
        }
        .info-bar .filter-badge {
            display: inline-block;
            background-color: #F0F0FF;
            color: #4B4BC9;
            border-radius: 10px;
            padding: 2px 10px;
            margin-right: 6px;
        }

        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data thead th {
            background-color: #696CFF;
            color: #ffffff;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 6px;
            text-align: left;
        }
        table.data tbody td {
            padding: 6px 6px;
            border-bottom: 1px solid #E9E9F2;
            vertical-align: top;
        }
        table.data tbody tr.row-alt td { background-color: #F7F7FB; }

        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-open { background-color: #FCE4E4; color: #C1121F; }
        .badge-in-progress { background-color: #FFF1DA; color: #B4690E; }
        .badge-done { background-color: #E2F5E9; color: #1B8A4A; }
        .badge-cancelled { background-color: #ECECEC; color: #6C6C6C; }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 8.5px;
            color: #9a9ab0;
            text-align: center;
            border-top: 1px solid #E9E9F2;
            padding-top: 6px;
        }

        .empty-state { text-align: center; color: #9a9ab0; padding: 24px 0; }
    </style>
</head>
<body>

<header>
    <div class="header-bar">
        <table>
            <tr>
                <td>
                    <div class="header-title">Laporan Log Permintaan</div>
                    <div class="header-subtitle">Group Information Technology and General Affair &ndash; PT BJB Sekuritas</div>
                </td>
                <td class="header-meta">
                    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
                    Total data: {{ $requests->count() }}
                </td>
            </tr>
        </table>
    </div>

    @if(!empty(array_filter($filters ?? [])))
        <div class="info-bar">
            Filter aktif:
            @if(!empty($filters['status'])) <span class="filter-badge">Status: {{ $filters['status'] }}</span> @endif
            @if(!empty($filters['bulan'])) <span class="filter-badge">Bulan: {{ $filters['bulan'] }}</span> @endif
            @if(!empty($filters['cari'])) <span class="filter-badge">Cari: "{{ $filters['cari'] }}"</span> @endif
        </div>
    @endif
</header>

<footer>
    Log Activity IT &amp; GA &middot; Dokumen ini digenerate otomatis dari sistem, mohon tidak diedit manual.
</footer>

<table class="data">
    <thead>
    <tr>
        <th style="width:3%">No</th>
        <th style="width:9%">Tanggal</th>
        <th style="width:14%">Kategori</th>
        <th style="width:20%">Permintaan</th>
        <th style="width:10%">Pemohon</th>
        <th style="width:24%">Status Permintaan</th>
        <th style="width:9%">Selesai</th>
        <th style="width:8%">Status</th>
    </tr>
    </thead>
    <tbody>
    @forelse($requests as $request)
        <tr class="{{ $loop->even ? 'row-alt' : '' }}">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $request->occurred_at->format('d/m/Y') }}</td>
            <td>{{ $request->category }}</td>
            <td>{{ $request->title }}</td>
            <td>{{ $request->requester }}</td>
            <td>{{ $request->follow_up }}</td>
            <td>{{ $request->completed_at?->format('d/m/Y') ?? '-' }}</td>
            <td>
                <span class="badge badge-{{ \Illuminate\Support\Str::slug($request->status->value) }}">
                    {{ $request->status->value }}
                </span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="empty-state">Tidak ada data untuk filter yang dipilih.</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
