<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Log Backup Data</title>
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

        .info-bar { margin-top: 8px; font-size: 9.5px; color: #5b5b73; }
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
        .center { text-align: center; }

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
                    <div class="header-title">Log Backup Data</div>
                    <div class="header-subtitle">Group Information Technology and General Affair &ndash; PT BJB Sekuritas</div>
                </td>
                <td class="header-meta">
                    Dicetak: {{ now()->locale('id')->translatedFormat('d F Y, H:i') }} WIB<br>
                    Total data: {{ $backupLogs->count() }}
                </td>
            </tr>
        </table>
    </div>

    @if(!empty(array_filter($filters ?? [])))
        <div class="info-bar">
            Filter aktif:
            @if(!empty($filters['jenis'])) <span class="filter-badge">Jenis: {{ $filters['jenis'] }}</span> @endif
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
        <th class="center" style="width:6%">No</th>
        <th style="width:30%">Jenis</th>
        <th style="width:20%">Tanggal</th>
        <th class="center" style="width:9%">Jam</th>
        <th style="width:35%">Keterangan</th>
    </tr>
    </thead>
    <tbody>
    @forelse($backupLogs as $item)
        <tr class="{{ $loop->even ? 'row-alt' : '' }}">
            <td class="center">{{ $loop->iteration }}</td>
            <td>{{ $item->jenis }}</td>
            <td>{{ $item->date_label }}</td>
            <td class="center">{{ $item->time_label }}</td>
            <td>{{ $item->notes ?: '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="empty-state">Tidak ada data untuk filter yang dipilih.</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
