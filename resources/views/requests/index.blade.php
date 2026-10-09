@extends('layouts.app')

@section('title', 'Log Permintaan')
@section('page-title', 'Log Permintaan')

@section('content')

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('requests.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Cari Permintaan</label>
                    <input type="text" name="cari" value="{{ $filters['cari'] ?? '' }}" class="form-control" placeholder="Judul permintaan...">
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        @foreach($statusOptions as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Bulan</label>
                    <input type="text" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="form-control" placeholder='contoh: "Juli 2026"'>
                </div>
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ti tabler-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Daftar Permintaan</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('requests.export.excel', $filters) }}" class="btn btn-outline-success btn-sm">
                    <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                </a>
                <a href="{{ route('requests.export.pdf', $filters) }}" class="btn btn-outline-danger btn-sm">
                    <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('requests.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti tabler-plus me-1"></i> Tambah Permintaan
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Terjadi</th>
                    <th>Kategori</th>
                    <th>Permintaan</th>
                    <th>Pemohon</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($requests as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($requests->currentPage() - 1) * $requests->perPage() }}</td>
                        <td>{{ $item->occurred_at->format('d/m/Y') }}</td>
                        <td>{{ $item->category }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($item->title, 40) }}</td>
                        <td>{{ $item->requester }}</td>
                        <td>
                            <span class="badge bg-label-{{ $item->status->badgeColor() }}">
                                {{ $item->status->value }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('requests.show', $item) }}" class="btn btn-icon btn-text-secondary" title="Lihat">
                                <i class="ti tabler-eye"></i>
                            </a>
                            <a href="{{ route('requests.edit', $item) }}" class="btn btn-icon btn-text-secondary" title="Edit">
                                <i class="ti tabler-edit"></i>
                            </a>
                            <form action="{{ route('requests.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin hapus permintaan ini?');">
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
                            Belum ada data permintaan. Klik "Tambah Permintaan" untuk mulai mencatat.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="card-footer">
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection
