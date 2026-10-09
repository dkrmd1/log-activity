@extends('layouts.app')

@section('title', 'Kontrol Server & Jaringan')
@section('page-title', 'Kontrol Server & Jaringan')

@section('content')

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Buat Form Baru</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('server-checks.store') }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="check_date" value="{{ old('check_date', now()->format('Y-m-d')) }}"
                                   class="form-control @error('check_date') is-invalid @enderror">
                            @error('check_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-plus me-1"></i> Buat / Buka Form
                            </button>
                        </div>
                    </form>
                    <p class="text-body-secondary small mb-0 mt-2">
                        Satu form per tanggal, berisi pemeriksaan pukul {{ implode(', ', $slots) }}.
                        Kalau form tanggal itu sudah ada, langsung dibuka.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Filter</h6></div>
                <div class="card-body">
                    <form method="GET" action="{{ route('server-checks.index') }}" class="row g-2 align-items-end">
                        <div class="col-sm-6">
                            <label class="form-label">Bulan</label>
                            <input type="text" name="bulan" value="{{ $bulan }}" class="form-control" placeholder='contoh: "September 2026"'>
                        </div>
                        <div class="col-sm-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="ti tabler-filter me-1"></i> Filter</button>
                            <a href="{{ route('server-checks.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="mb-0">Riwayat Form Kontrol</h6></div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Tanggal</th>
                    @foreach($slots as $label)
                        <th class="text-center">{{ $label }}</th>
                    @endforeach
                    <th>Petugas</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($checks as $check)
                    @php
                        $total = $check->entries->count();
                        $filled = $check->filledCounts();
                        $petugas = $check->entries->pluck('parafUser.name')->filter()->unique()->implode(', ');
                    @endphp
                    <tr>
                        <td>{{ $check->date_label }}</td>
                        @foreach($slots as $n => $label)
                            <td class="text-center">
                                <span class="badge bg-label-{{ $filled[$n] === 0 ? 'secondary' : ($filled[$n] >= $total ? 'success' : 'warning') }}">
                                    {{ $filled[$n] }}/{{ $total }}
                                </span>
                            </td>
                        @endforeach
                        <td>{{ $petugas ?: '-' }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('server-checks.edit', $check) }}" class="btn btn-icon btn-text-secondary" title="Isi / Edit">
                                <i class="ti tabler-edit"></i>
                            </a>
                            <button type="button" class="btn btn-icon btn-text-primary" title="Lihat"
                                    data-bs-toggle="modal" data-bs-target="#previewModal"
                                    data-preview-url="{{ route('server-checks.preview', $check) }}">
                                <i class="ti tabler-eye"></i>
                            </button>
                            <a href="{{ route('server-checks.pdf', $check) }}" class="btn btn-icon btn-text-danger" title="Download PDF">
                                <i class="ti tabler-file-type-pdf"></i>
                            </a>
                            <form action="{{ route('server-checks.destroy', $check) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus form kontrol tanggal {{ $check->date_label }}?');">
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
                        <td colspan="{{ count($slots) + 3 }}" class="text-center text-body-secondary py-4">
                            Belum ada form kontrol. Pilih tanggal di atas lalu klik "Buat / Buka Form".
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($checks->hasPages())
            <div class="card-footer">
                {{ $checks->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @include('server-checks._preview_modal')

@endsection
