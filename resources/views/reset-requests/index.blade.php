@extends('layouts.app')

@section('title', 'Permohonan Reset')
@section('page-title', 'Permohonan Reset (CS)')

@section('content')

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reset-requests.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-6 col-md-3">
                    <label class="form-label">Cari</label>
                    <input type="text" name="cari" value="{{ $filters['cari'] ?? '' }}" class="form-control" placeholder="Nama / User ID / Email...">
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
                    <input type="text" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="form-control" placeholder='contoh: "September 2026"'>
                </div>
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="ti tabler-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('reset-requests.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Daftar Permohonan</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('reset-requests.export.excel', $filters) }}" class="btn btn-outline-success btn-sm">
                    <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                </a>
                <a href="{{ route('reset-requests.export.pdf', $filters) }}" class="btn btn-outline-danger btn-sm">
                    <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
                </a>
                <a href="{{ route('reset-requests.create') }}" class="btn btn-primary btn-sm">
                    <i class="ti tabler-plus me-1"></i> Tambah Permohonan
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>User ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jenis Permohonan</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($resetRequests as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($resetRequests->currentPage() - 1) * $resetRequests->perPage() }}</td>
                        <td>{{ $item->requested_at->format('d/m/Y') }}</td>
                        <td>{{ $item->account_user_id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->email }}</td>
                        <td>
                            @foreach($item->types as $type)
                                <span class="badge bg-label-info mb-1">{{ $type }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $item->status->badgeColor() }}">
                                {{ $item->status->value }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('reset-requests.show', $item) }}" class="btn btn-icon btn-text-secondary" title="Lihat">
                                <i class="ti tabler-eye"></i>
                            </a>
                            <a href="{{ route('reset-requests.edit', $item) }}" class="btn btn-icon btn-text-secondary" title="Edit">
                                <i class="ti tabler-edit"></i>
                            </a>
                            <form action="{{ route('reset-requests.destroy', $item) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin hapus permohonan ini?');">
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
                        <td colspan="8" class="text-center text-body-secondary py-4">
                            Belum ada data permohonan. Klik "Tambah Permohonan" untuk mulai mencatat.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($resetRequests->hasPages())
            <div class="card-footer">
                {{ $resetRequests->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection
