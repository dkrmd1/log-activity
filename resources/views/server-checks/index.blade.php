@extends('layouts.app')

@section('title', 'Kontrol Server & Jaringan')
@section('page-title', 'Kontrol Server & Jaringan')

@section('content')

    @php $canFill = auth()->user()->isStaff(); @endphp

    @error('reject_note') <div class="alert alert-danger">{{ $message }}</div> @enderror

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">Buat Form Baru</h6></div>
                <div class="card-body">
                    @if($canFill)
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
                    @else
                        <p class="text-body-secondary mb-0">
                            Form harian dibuat dan diisi oleh <strong>Staf</strong>
                            (kolom Paraf memakai tanda tangan staf yang mengisi).
                            @if(auth()->user()->isGroupHead())
                                Sebagai Group Head, gunakan tombol <em>Approve</em> atau <em>Reject</em>
                                pada riwayat di bawah untuk form yang sudah terisi.
                            @endif
                        </p>
                    @endif
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
                    <th>Tanda Tangan</th>
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
                        <td>
                            @if($check->isSigned())
                                <span class="badge bg-label-success" title="{{ $check->signed_name }}">Sudah &middot; {{ $check->signed_name }}</span>
                            @elseif($check->isRejected())
                                <span class="badge bg-label-danger" title="{{ $check->reject_note }}">Ditolak &middot; {{ $check->rejected_name }}</span>
                            @else
                                <span class="badge bg-label-secondary">Belum</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('server-checks.edit', $check) }}" class="btn btn-icon btn-text-secondary" title="{{ $canFill ? 'Isi / Edit' : (auth()->user()->isGroupHead() ? 'Lihat / Approve' : 'Lihat') }}">
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
                            @if(auth()->user()->isGroupHead())
                                {{-- Group Head: Approve / Reject (bukan Hapus). Form yang sudah di-approve dibatalkan lewat halaman form. --}}
                                @unless($check->isSigned())
                                    <form action="{{ route('server-checks.sign', $check) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Approve form tanggal {{ $check->date_label }}? Tanda tangan Anda akan dibubuhkan.');">
                                        @csrf
                                        <button type="submit" class="btn btn-icon btn-text-success" title="Approve">
                                            <i class="ti tabler-check"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-icon btn-text-danger" title="Reject"
                                            data-bs-toggle="modal" data-bs-target="#rejectModal"
                                            data-reject-url="{{ route('server-checks.reject', $check) }}"
                                            data-reject-date="{{ $check->date_label }}">
                                        <i class="ti tabler-arrow-back-up"></i>
                                    </button>
                                @endunless
                            @else
                                <form action="{{ route('server-checks.destroy', $check) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hapus form kontrol tanggal {{ $check->date_label }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-text-danger" title="Hapus">
                                        <i class="ti tabler-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($slots) + 4 }}" class="text-center text-body-secondary py-4">
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

    @if(auth()->user()->isGroupHead())
        {{-- Modal Reject: keterangan WAJIB diisi --}}
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" id="rejectForm" action="#">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Reject form <span id="rejectDate"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Keterangan penolakan <span class="text-danger">*</span></label>
                            <textarea name="reject_note" rows="3" maxlength="1000" class="form-control" required
                                      placeholder="Apa yang perlu diperbaiki Staf?"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Reject &amp; Kembalikan ke Staf</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection

@if(auth()->user()->isGroupHead())
    @push('scripts')
    <script>
    (function () {
        var modal = document.getElementById('rejectModal');
        if (!modal) return;
        modal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            if (!btn) return;
            document.getElementById('rejectForm').setAttribute('action', btn.getAttribute('data-reject-url'));
            document.getElementById('rejectDate').textContent = btn.getAttribute('data-reject-date') || '';
            modal.querySelector('textarea[name="reject_note"]').value = '';
        });
    })();
    </script>
    @endpush
@endif
