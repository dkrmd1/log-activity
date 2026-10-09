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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0">Tanggal, {{ $check->date_label }}</h6>
                <small class="text-body-secondary">Isi kolom Kondisi &amp; Keterangan sesuai waktu pemeriksaan. Paraf terisi otomatis dari tanda tangan Anda.</small>
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
                                <button type="button" class="btn btn-outline-success ms-1 js-all-ok" style="padding:0 .45rem; font-size:.7rem;" data-slot="{{ $n }}"
                                        title="Isi semua Kondisi yang masih kosong dengan OK">Semua OK</button>
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
                                        <select name="entries[{{ $e->id }}][c{{ $n }}]" class="form-select form-select-sm js-cond" data-slot="{{ $n }}">
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
                                                          class="form-control form-control-sm"
                                                          placeholder="Keterangan {{ $label }} (satu untuk seluruh bagian {{ $code }})">{{ $e->{'n' . $n} }}</textarea>
                                            </td>
                                        @endif
                                    @else
                                        <td class="note">
                                            <textarea name="entries[{{ $e->id }}][n{{ $n }}]" rows="2"
                                                      class="form-control form-control-sm"
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
                <button type="submit" class="btn btn-primary">
                    <i class="ti tabler-device-floppy me-1"></i> Simpan
                </button>
                <a href="{{ route('server-checks.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
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
