@extends('layouts.app')

@section('title', 'Item Kontrol Server')
@section('page-title', 'Item Kontrol Server & Jaringan')

@section('content')

    <div class="alert alert-info">
        Atur bagian (A, B, C, ...) dan item pemeriksaan pada form <strong>Kontrol Server &amp; Jaringan</strong>.
        Perubahan hanya berlaku untuk <strong>form baru</strong>; form yang sudah dibuat tidak berubah.
        Item yang dinonaktifkan tidak muncul di form baru. Jika "Keterangan digabung" aktif, kolom Keterangan
        satu bagian dijadikan 1 sel besar (seperti bagian A).
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Tambah Bagian Baru</h6></div>
        <div class="card-body">
            <form method="POST" action="{{ route('control-items.sections.store') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-sm-2">
                    <label class="form-label">Kode</label>
                    <input type="text" name="code" value="{{ old('code') }}" maxlength="5" class="form-control @error('code') is-invalid @enderror" placeholder="D">
                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Judul bagian</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Pemeriksaan ...">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-sm-2">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="merge_notes" value="1" id="newMerge" class="form-check-input">
                        <label for="newMerge" class="form-check-label">Keterangan digabung</label>
                    </div>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-plus me-1"></i> Tambah</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($sections as $section)
        <div class="card mb-4">
            <div class="card-header">
                <form method="POST" action="{{ route('control-items.sections.update', $section) }}" class="row g-2 align-items-end">
                    @csrf
                    @method('PUT')
                    <div class="col-sm-1">
                        <label class="form-label small">Kode</label>
                        <input type="text" name="code" value="{{ $section->code }}" maxlength="5" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label small">Judul bagian</label>
                        <input type="text" name="title" value="{{ $section->title }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-1">
                        <label class="form-label small">Urutan</label>
                        <input type="number" name="sort_order" value="{{ $section->sort_order }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-sm-2">
                        <div class="form-check mb-1">
                            <input type="checkbox" name="merge_notes" value="1" id="merge{{ $section->id }}" class="form-check-input" @checked($section->merge_notes)>
                            <label for="merge{{ $section->id }}" class="form-check-label small">Keterangan digabung</label>
                        </div>
                    </div>
                    <div class="col-sm-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="ti tabler-device-floppy me-1"></i> Simpan</button>
                        <button type="submit" form="delSection{{ $section->id }}" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Hapus bagian {{ $section->code }} beserta semua itemnya? (Form yang sudah dibuat tidak terpengaruh)');">
                            <i class="ti tabler-trash"></i>
                        </button>
                    </div>
                </form>
                <form id="delSection{{ $section->id }}" method="POST" action="{{ route('control-items.sections.destroy', $section) }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nama item</th>
                        <th style="width:100px">Urutan</th>
                        <th style="width:90px">Aktif</th>
                        <th style="width:150px" class="text-end">Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($section->items as $item)
                        <tr>
                            <td>
                                {{ $loop->iteration }}
                                <form method="POST" action="{{ route('control-items.items.update', $item) }}" id="item{{ $item->id }}" class="d-none">
                                    @csrf
                                    @method('PUT')
                                </form>
                            </td>
                            <td><input type="text" name="name" form="item{{ $item->id }}" value="{{ $item->name }}" class="form-control form-control-sm"></td>
                            <td><input type="number" name="sort_order" form="item{{ $item->id }}" value="{{ $item->sort_order }}" class="form-control form-control-sm"></td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" name="is_active" value="1" form="item{{ $item->id }}" class="form-check-input" @checked($item->is_active)>
                                </div>
                            </td>
                            <td class="text-end text-nowrap">
                                <button type="submit" form="item{{ $item->id }}" class="btn btn-sm btn-outline-primary" title="Simpan">
                                    <i class="ti tabler-device-floppy"></i>
                                </button>
                                <button type="submit" form="delItem{{ $item->id }}" class="btn btn-sm btn-outline-danger" title="Hapus"
                                        onclick="return confirm('Hapus item ini?');">
                                    <i class="ti tabler-trash"></i>
                                </button>
                                <form id="delItem{{ $item->id }}" method="POST" action="{{ route('control-items.items.destroy', $item) }}" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-body-secondary py-3">Belum ada item di bagian ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body border-top">
                <form method="POST" action="{{ route('control-items.items.store', $section) }}" class="row g-2">
                    @csrf
                    <div class="col-sm-9">
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Tambah item baru di bagian {{ $section->code }}...">
                    </div>
                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="ti tabler-plus me-1"></i> Tambah Item</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection
