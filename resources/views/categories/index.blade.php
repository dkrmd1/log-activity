@extends('layouts.app')

@section('title', 'Kelola Kategori')
@section('page-title', 'Kelola Kategori & Pilihan')

@section('content')

    <ul class="nav nav-pills mb-4">
        @foreach($types as $key => $label)
            <li class="nav-item">
                <a class="nav-link {{ $type === $key ? 'active' : '' }}" href="{{ route('categories.index', ['type' => $key]) }}">{{ $label }}</a>
            </li>
        @endforeach
    </ul>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Tambah {{ $types[$type] }} Baru</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('categories.store') }}" class="row g-3 align-items-start">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="col-sm-8 col-md-6">
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror"
                           placeholder="Ketik nama baru...">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-plus me-1"></i> Tambah
                    </button>
                </div>
            </form>
            <p class="text-body-secondary small mb-0 mt-2">
                @switch($type)
                    @case('isu') Setelah ditambah, langsung muncul sebagai pilihan di form "Tambah Isu" / "Edit Isu". @break
                    @case('permintaan') Setelah ditambah, langsung muncul sebagai pilihan di form "Tambah Permintaan" / "Edit Permintaan". @break
                    @case('backup') Setelah ditambah, langsung muncul di form "Catat Backup" dan di tabel Status Backup. @break
                @endswitch
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Daftar {{ $types[$type] }} ({{ $categories->count() }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th>Nama</th>
                    <th style="width:140px">Dipakai</th>
                    <th class="text-end" style="width:120px">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $i => $category)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <form method="POST" action="{{ route('categories.update', $category) }}" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="type" value="{{ $category->type }}">
                                <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" style="max-width:320px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan perubahan nama">
                                    <i class="ti tabler-device-floppy"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $usage[$category->id] > 0 ? 'info' : 'secondary' }}">{{ $usage[$category->id] }} data</span>
                        </td>
                        <td class="text-end">
                            @if($usage[$category->id] > 0)
                                <button type="button" class="btn btn-icon btn-text-secondary" disabled
                                        title="Masih dipakai di data, tidak bisa dihapus">
                                    <i class="ti tabler-trash"></i>
                                </button>
                            @else
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus &quot;{{ addslashes($category->name) }}&quot;?');">
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
                        <td colspan="4" class="text-center text-body-secondary py-4">
                            Belum ada data. Tambahkan lewat form di atas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
