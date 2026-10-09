@extends('layouts.app')

@section('title', 'Kategori Isu')
@section('page-title', 'Kategori Isu')

@section('content')

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Tambah Kategori Baru</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('issue-categories.store') }}" class="row g-3 align-items-start">
                @csrf
                <div class="col-sm-8 col-md-6">
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror"
                           placeholder="Contoh: Perangkat Keras (Hardware)">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-plus me-1"></i> Tambah
                    </button>
                </div>
            </form>
            <p class="text-body-secondary small mb-0 mt-2">
                Setelah ditambah, kategori ini langsung muncul sebagai pilihan di form "Tambah Isu" / "Edit Isu".
            </p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">Daftar Kategori ({{ $categories->count() }})</h6>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th style="width:5%">No</th>
                    <th>Nama Kategori</th>
                    <th class="text-end" style="width:180px">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $i => $category)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <form method="POST" action="{{ route('issue-categories.update', $category) }}" class="d-flex gap-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" style="max-width:320px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Simpan perubahan nama">
                                    <i class="ti tabler-device-floppy"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('issue-categories.destroy', $category) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin hapus kategori \'{{ $category->name }}\'?');">
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
                        <td colspan="3" class="text-center text-body-secondary py-4">
                            Belum ada kategori. Tambahkan dulu lewat form di atas.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
