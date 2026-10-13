@extends('layouts.app')

@section('title', 'Form Kontrol Server & Jaringan')
@section('page-title', 'Kontrol Server & Jaringan')

@push('styles')
<style>
    .sc-table th, .sc-table td { vertical-align: middle; }
    .sc-table .cond { min-width: 78px; }
    .sc-table .note { min-width: 170px; }
    .sc-table .sec-row td { background: rgba(105,108,255,.10); font-weight: 600; }
</style>
@endpush

@section('content')

    @php
        // Staf mengisi form (Paraf = tanda tangan staf). Group Head hanya menandatangani. Admin hanya melihat.
        $canFill = auth()->user()->isStaff();
    @endphp

    @if($check->isRejected())
        <div class="alert alert-danger">
            <strong>Ditolak oleh {{ $check->rejected_name }}</strong>
            <span class="small">({{ $check->rejected_at->locale('id')->translatedFormat('d F Y, H:i') }})</span><br>
            {!! nl2br(e($check->reject_note)) !!}
            @if($canFill)
                <hr class="my-2">
                <span class="small">Perbaiki isi form sesuai keterangan di atas lalu klik Simpan. Group Head akan otomatis diberi tahu untuk memeriksa ulang.</span>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0">Tanggal, {{ $check->date_label }}</h6>
                @if($canFill)
                    <small class="text-body-secondary">Isi kolom Kondisi &amp; Keterangan sesuai waktu pemeriksaan. Paraf terisi otomatis dari tanda tangan Anda.</small>
                @else
                    <small class="text-body-secondary">Hanya Staf yang dapat mengisi form ini. Kolom Paraf terisi dari tanda tangan staf yang mengisi.</small>
                @endif
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm"
                        data-bs-toggle="modal" data-bs-target="#previewModal"
                        data-preview-url="{{ route('server-checks.preview', $check) }}">
                    <i class="ti tabler-eye me-1"></i> Lihat
                </button>
                <a href="{{ route('server-checks.pdf', $check) }}" class="btn btn-outline-danger btn-sm">
                    <i class="ti tabler-file-type-pdf me-1"></i> PDF
                </a>
                <a href="{{ route('server-checks.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('server-checks.update', $check) }}">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table table-bordered table-sm sc-table mb-0">
                    <thead>
                    <tr class="table-light">
                        <th rowspan="2" class="text-center" style="width:44px">No</th>
                        <th rowspan="2" colspan="2">Item</th>
                        @foreach($slots as $n => $label)
                            <th colspan="2" class="text-center">
                                {{ $label }}
                                @if($canFill)
                                    <button type="button" class="btn btn-outline-success ms-1 js-all-ok" style="padding:0 .45rem; font-size:.7rem;" data-slot="{{ $n }}"
                                            title="Isi semua Kondisi yang masih kosong dengan OK">Semua OK</button>
                                @endif
                            </th>
                        @endforeach
                        <th rowspan="2" class="text-center" style="width:130px">Paraf</th>
                    </tr>
                    <tr class="table-light">
                        @foreach($slots as $n => $label)
                            <th class="text-center">Kondisi</th>
                            <th class="text-center">Keterangan</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($groups as $code => $rows)
                        @php
                            $first = $rows->first();
                            $count = $rows->count();
                        @endphp
                        <tr class="sec-row">
                            <td class="text-center" rowspan="{{ $count + 1 }}">{{ $code }}</td>
                            <td colspan="9">{{ $first->section_title }}</td>
                        </tr>
                        @foreach($rows as $ri => $e)
                            <tr>
                                <td class="text-center">{{ $e->item_no }}</td>
                                <td>{{ $e->item_name }}</td>
                                @foreach($slots as $n => $label)
                                    <td class="cond">
                                        <select name="entries[{{ $e->id }}][c{{ $n }}]" class="form-select form-select-sm js-cond" data-slot="{{ $n }}" @disabled(! $canFill)>
                                            <option value=""></option>
                                            @foreach(\App\Models\ServerCheckEntry::CONDITIONS as $opt)
                                                <option value="{{ $opt }}" @selected($e->{'c' . $n} === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    @if($first->merge_notes)
                                        @if($ri === 0)
                                            <td class="note" rowspan="{{ $count }}">
                                                <textarea name="entries[{{ $e->id }}][n{{ $n }}]" rows="{{ min(max($count, 3), 10) }}"
                                                          class="form-control form-control-sm" @disabled(! $canFill)
                                                          placeholder="Keterangan {{ $label }} (satu untuk seluruh bagian {{ $code }})">{{ $e->{'n' . $n} }}</textarea>
                                            </td>
                                        @endif
                                    @else
                                        <td class="note">
                                            <textarea name="entries[{{ $e->id }}][n{{ $n }}]" rows="2"
                                                      class="form-control form-control-sm" @disabled(! $canFill)
                                                      placeholder="Keterangan">{{ $e->{'n' . $n} }}</textarea>
                                        </td>
                                    @endif
                                @endforeach
                                <td class="text-center small">{{ $e->parafUser?->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>

            @error('entries.*') <div class="text-danger small px-4 pt-3">{{ $message }}</div> @enderror

            <div class="card-body">
                @if($canFill)
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Simpan
                    </button>
                @endif
                <a href="{{ route('server-checks.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>

    {{-- Tanda tangan form (muncul di kanan bawah PDF). HANYA Group Head yang bisa menandatangani. --}}
    <div class="card mt-4">
        <div class="card-header"><h6 class="mb-0">Tanda Tangan Group Head</h6></div>
        <div class="card-body">
            @if($check->isSigned())
                <div class="d-flex flex-wrap align-items-center gap-4">
                    <div class="border rounded p-2 bg-white">
                        <img src="{{ $check->signed_signature_uri }}" alt="Tanda tangan" style="max-height:80px; max-width:100%;">
                    </div>
                    <div>
                        <div class="fw-medium">{{ $check->signed_name }}</div>
                        <div class="small text-body-secondary">
                            Ditandatangani {{ $check->signed_at->locale('id')->translatedFormat('d F Y, H:i') }}
                        </div>
                        @if(auth()->user()->isGroupHead())
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" data-bs-toggle="collapse" data-bs-target="#rejectBox">
                                <i class="ti tabler-arrow-back-up me-1"></i> Reject
                            </button>
                        @endif
                    </div>
                </div>
                <p class="text-body-secondary small mt-3 mb-0">
                    Kalau isi form diubah setelah ini, tanda tangan otomatis dihapus dan akan terpasang otomatis lagi saat Group Head membuka form yang sudah lengkap.
                </p>
            @elseif(auth()->user()->isGroupHead())
                <p class="text-body-secondary">
                    Tanda tangan Anda <strong>terpasang otomatis</strong> saat Anda membuka form yang sudah lengkap
                    (ketiga waktu pemeriksaan terisi).
                    @if($check->isRejected())
                        Form ini sedang ditolak, jadi menunggu Staf merevisi.
                    @else
                        Form ini belum lengkap, jadi belum ditandatangani.
                    @endif
                </p>
                @if(! auth()->user()->signature)
                    <div class="alert alert-warning py-2">
                        Tanda tangan Anda belum diisi. <a href="{{ route('profile.edit') }}" class="alert-link">Isi di Profil Saya</a> dulu.
                    </div>
                @endif
                @unless($check->isRejected())
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#rejectBox">
                        <i class="ti tabler-arrow-back-up me-1"></i> Reject
                    </button>
                @endunless
            @else
                <p class="text-body-secondary mb-0">
                    Form ini belum ditandatangani. Blok tanda tangan di kanan bawah PDF hanya diisi oleh Group Head
                    (terpasang otomatis saat Group Head membuka form yang sudah lengkap).
                </p>
            @endif

            @if(auth()->user()->isGroupHead() && ! $check->isRejected())
                @error('reject_note') <div class="alert alert-danger mt-3 mb-0">{{ $message }}</div> @enderror

                <div class="collapse mt-3 {{ $errors->has('reject_note') ? 'show' : '' }}" id="rejectBox">
                    <form method="POST" action="{{ route('server-checks.reject', $check) }}">
                        @csrf
                        <label class="form-label">Keterangan penolakan <span class="text-danger">*</span></label>
                        <textarea name="reject_note" rows="3" maxlength="1000" class="form-control mb-2" placeholder="Apa yang perlu diperbaiki Staf?" required>{{ old('reject_note') }}</textarea>
                        <button type="submit" class="btn btn-danger btn-sm">Reject &amp; Kembalikan ke Staf</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    @include('server-checks._preview_modal')

@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-all-ok').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var slot = btn.getAttribute('data-slot');
        document.querySelectorAll('select.js-cond[data-slot="' + slot + '"]').forEach(function (sel) {
            if (sel.value === '') sel.value = 'OK';
        });
    });
});
</script>
@endpush
