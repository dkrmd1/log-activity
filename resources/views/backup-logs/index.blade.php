@extends('layouts.app')

@section('title', 'Log Backup')
@section('page-title', 'Log Backup Data')

@section('content')

    {{-- Ringkasan: backup terakhir tiap jenis di bulan terpilih --}}
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Status Backup &mdash; {{ $summaryLabel }}</h6>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th style="width:60px">No</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($jenisOptions as $jenis)
                    @php($last = $latestPerJenis->get($jenis))
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $jenis }}</td>
                        <td>{{ $last?->date_label ?? '-' }}</td>
                        <td>{{ $last?->time_label ?? '-' }}</td>
                        <td>
                            @if($last)
                                <span class="badge bg-label-success">Sudah</span>
                            @else
                                <span class="badge bg-label-secondary">Belum</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('backup-logs.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="cari" value="{{ $filters['cari'] ?? '' }}" class="form-control" placeholder="Jenis / keterangan...">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Jenis</label>
                    <select name="jenis" class="form-select">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisOptions as $jenis)
                            <option value="{{ $jenis }}" @selected(($filters['jenis'] ?? '') === $jenis)>{{ $jenis }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Bulan</label>
                    <input type="text" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="form-control" placeholder='contoh: "September 2026"'>
                </div>
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ti tabler-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('backup-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Riwayat Backup</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('backup-logs.export.excel', $filters) }}" class="btn btn-outline-success btn-sm">
                    <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                </a>
                <a href="{{ route('backup-logs.export.pdf', $filters) }}" class="btn btn-outline-danger btn-sm">
                    <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('backup-logs.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti tabler-plus me-1"></i> Catat Backup
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Keterangan</th>
                    <th>Dicatat Oleh</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($backupLogs as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($backupLogs->currentPage() - 1) * $backupLogs->perPage() }}</td>
                        <td>{{ $item->jenis }}</td>
                        <td>{{ $item->date_label }}</td>
                        <td>{{ $item->time_label }}</td>
                        <td>{{ $item->notes ?: '-' }}</td>
                        <td>{{ $item->user?->name ?? '-' }}</td>
                        <td class="text-end">
                            <a href="{{ route('backup-logs.edit', $item) }}" class="btn btn-icon btn-text-secondary" title="Edit">
                                <i class="ti tabler-edit"></i>
                            </a>
                            <form action="{{ route('backup-logs.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin hapus catatan backup ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-text-danger" title="Hapus">
                                    <i class="ti tabler-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-body-secondary py-4">
                            Belum ada catatan backup. Klik "Catat Backup" untuk mulai mencatat.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($backupLogs->hasPages())
            <div class="card-footer">
                {{ $backupLogs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection
