@extends('layouts.app')

@section('title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan')

@php
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

@section('content')

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Pilih Periode Laporan</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                <div class="col-sm-4 col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @foreach($namaBulan as $num => $label)
                            <option value="{{ $num }}" @selected($bulan == $num)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4 col-md-3">
                    <label class="form-label">Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-refresh me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= STATUS PERSETUJUAN ================= --}}
    @php
        $isStaff = auth()->user()->isStaff();
        $isGH = auth()->user()->isGroupHead();
        $status = $approval?->status;

        if (! $approval) {
            $badge = ['secondary', 'Belum diajukan'];
        } elseif ($dataChanged) {
            $badge = ['warning', 'Data berubah — perlu diajukan ulang'];
        } elseif ($status === 'submitted') {
            $badge = ['warning', 'Menunggu persetujuan Group Head'];
        } elseif ($status === 'approved') {
            $badge = ['success', 'Disetujui'];
        } else {
            $badge = ['danger', 'Dikembalikan — perlu revisi'];
        }

        $hasData = ($summary['total'] + $summary['backups'] + $summary['controls']) > 0;
        $canSubmit = $isStaff && $hasData && (! $approval || $status === 'rejected' || $dataChanged);
        $canReview = $isGH && $approval && $status === 'submitted' && ! $dataChanged;
    @endphp

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Persetujuan Laporan — {{ $periodLabel }}</h6>
            <span class="badge bg-label-{{ $badge[0] }}">{{ $badge[1] }}</span>
        </div>
        <div class="card-body">

            @if($approval)
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="text-body-secondary small">Dibuat oleh</div>
                        <div class="fw-medium">{{ $approval->prepared_name }}</div>
                        <div class="small text-body-secondary">{{ $approval->prepared_position }} &middot; {{ $approval->prepared_at->locale('id')->translatedFormat('d F Y, H:i') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-body-secondary small">Diketahui oleh</div>
                        @if($status === 'approved')
                            <div class="fw-medium">{{ $approval->reviewed_name }}</div>
                            <div class="small text-body-secondary">{{ $approval->reviewed_position }} &middot; {{ $approval->reviewed_at->locale('id')->translatedFormat('d F Y, H:i') }}</div>
                        @else
                            <div class="text-body-secondary">—</div>
                        @endif
                    </div>
                </div>

                @if($status === 'rejected')
                    <div class="alert alert-danger">
                        <strong>Dikembalikan oleh {{ $approval->reviewed_name }}:</strong><br>
                        {{ $approval->review_note }}
                    </div>
                @endif

                @if($dataChanged)
                    <div class="alert alert-warning">
                        Data Log Isu / Permintaan / Reset / Backup bulan ini <strong>berubah</strong> sejak laporan diajukan,
                        jadi tanda tangan tidak berlaku lagi untuk data terbaru (PDF diberi watermark DRAFT).
                        @if($isStaff) Silakan ajukan ulang. @else Staf perlu mengajukan ulang. @endif
                    </div>
                @endif
            @elseif(! $hasData)
                <p class="text-body-secondary mb-0">Belum ada data di periode ini.</p>
            @else
                <p class="text-body-secondary">Laporan periode ini belum diajukan ke Group Head.</p>
            @endif

            @if(($canSubmit || $canReview) && ! $hasSignature)
                <div class="alert alert-warning">
                    Tanda tangan Anda belum diisi. <a href="{{ route('profile.edit') }}" class="alert-link">Isi di Profil Saya</a>
                    dulu sebelum {{ $canSubmit ? 'mengajukan' : 'menyetujui' }} laporan.
                </div>
            @endif

            @error('review_note') <div class="alert alert-danger">{{ $message }}</div> @enderror

            <div class="d-flex flex-wrap gap-2">
                @if($approval || $hasData)
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#previewModal">
                        <i class="ti tabler-eye me-1"></i> Lihat Laporan
                    </button>
                @endif

                @if($canSubmit)
                    <form method="POST" action="{{ route('reports.submit') }}"
                          onsubmit="return confirm('Ajukan laporan {{ $periodLabel }} ke Group Head? Tanda tangan Anda akan dibubuhkan.');">
                        @csrf
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <button type="submit" class="btn btn-primary" @disabled(! $hasSignature)>
                            <i class="ti tabler-send me-1"></i> {{ $approval ? 'Ajukan Ulang ke Group Head' : 'Ajukan ke Group Head' }}
                        </button>
                    </form>
                @endif

                @if($canReview)
                    <form method="POST" action="{{ route('reports.approve') }}"
                          onsubmit="return confirm('Setujui laporan {{ $periodLabel }}? Tanda tangan Anda akan dibubuhkan.');">
                        @csrf
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <button type="submit" class="btn btn-success" @disabled(! $hasSignature)>
                            <i class="ti tabler-check me-1"></i> Setujui (ACC)
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rejectBox">
                        <i class="ti tabler-arrow-back-up me-1"></i> Tolak / Minta Revisi
                    </button>
                @endif
            </div>

            @if($canReview)
                <div class="collapse mt-3 {{ $errors->has('review_note') ? 'show' : '' }}" id="rejectBox">
                    <form method="POST" action="{{ route('reports.reject') }}">
                        @csrf
                        <input type="hidden" name="bulan" value="{{ $bulan }}">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <label class="form-label">Catatan untuk Staf <span class="text-danger">*</span></label>
                        <textarea name="review_note" rows="3" class="form-control mb-2" placeholder="Apa yang perlu diperbaiki?">{{ old('review_note') }}</textarea>
                        <button type="submit" class="btn btn-danger btn-sm">Kembalikan ke Staf</button>
                    </form>
                </div>
            @endif

        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0">Ringkasan — {{ $periodLabel }}</h6>
            <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#previewModal">
                <i class="ti tabler-eye me-1"></i> Lihat Laporan
            </button>
            <a href="{{ route('reports.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-danger btn-sm">
                <i class="ti tabler-file-type-pdf me-1"></i> Download PDF{{ ($approval?->status === 'approved' && ! $dataChanged) ? '' : ' (Draft)' }}
            </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-4 mb-2">
                <div class="col-sm-6 col-lg">
                    <div class="card h-100 bg-label-primary">
                        <div class="card-body">
                            <p class="mb-1">Log Isu</p>
                            <h4 class="mb-0">{{ $summary['issues'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="card h-100 bg-label-info">
                        <div class="card-body">
                            <p class="mb-1">Log Permintaan</p>
                            <h4 class="mb-0">{{ $summary['requests'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="card h-100 bg-label-warning">
                        <div class="card-body">
                            <p class="mb-1">Permohonan Reset</p>
                            <h4 class="mb-0">{{ $summary['reset_requests'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="card h-100 bg-label-success">
                        <div class="card-body">
                            <p class="mb-1">Log Backup Data</p>
                            <h4 class="mb-0">{{ $summary['backups'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="card h-100 bg-label-info">
                        <div class="card-body">
                            <p class="mb-1">Kontrol Server (hari)</p>
                            <h4 class="mb-0">{{ $summary['controls'] }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg">
                    <div class="card h-100 bg-label-dark">
                        <div class="card-body">
                            <p class="mb-1">Total Semua Data</p>
                            <h4 class="mb-0">{{ $summary['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-body-secondary small mb-0">
                Catatan: Total Semua Data dan status tidak mencakup Log Backup Data &amp; Kontrol Server. Status: <span class="badge bg-label-danger">Open {{ $summary['open'] }}</span>
                <span class="badge bg-label-warning">In Progress {{ $summary['in_progress'] }}</span>
                <span class="badge bg-label-success">Done {{ $summary['done'] }}</span>
                <span class="badge bg-label-secondary">Cancelled {{ $summary['cancelled'] }}</span>
            </p>

            @if($summary['total'] === 0 && $summary['backups'] === 0 && $summary['controls'] === 0)
                <div class="alert alert-warning mt-3 mb-0">
                    Tidak ada data Log Isu, Log Permintaan, Permohonan Reset, Log Backup Data, maupun Kontrol Server &amp; Jaringan di bulan {{ $periodLabel }}. PDF tetap bisa didownload, tapi tabelnya akan kosong.
                </div>
            @endif
        </div>
    </div>


    {{-- ================= PRATINJAU LAPORAN (tanpa download) ================= --}}
    @php($previewUrl = route('reports.preview', ['bulan' => $bulan, 'tahun' => $tahun]))
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true" data-preview-url="{{ $previewUrl }}">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pratinjau Laporan — {{ $periodLabel }}</h5>
                    <a href="{{ $previewUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary ms-auto me-3">
                        <i class="ti tabler-external-link me-1"></i> Buka di tab baru
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-0 position-relative">
                    <div id="previewLoading" class="position-absolute top-50 start-50 translate-middle text-center text-body-secondary">
                        <div class="spinner-border mb-2" role="status"></div>
                        <div>Menyiapkan laporan… (bisa beberapa detik)</div>
                    </div>
                    <iframe id="previewFrame" title="Pratinjau laporan" style="width:100%; height:100%; border:0; position:relative;"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('previewModal');
    var frame = document.getElementById('previewFrame');
    var loading = document.getElementById('previewLoading');
    if (!modal || !frame) return;

    // Dimuat ulang tiap dibuka, supaya selalu menampilkan data & status terbaru.
    modal.addEventListener('show.bs.modal', function () {
        loading.style.display = '';
        frame.onload = function () { loading.style.display = 'none'; };
        frame.src = modal.getAttribute('data-preview-url');
    });
    modal.addEventListener('hidden.bs.modal', function () {
        frame.onload = null;
        frame.src = 'about:blank';
    });
})();
</script>
@endpush
