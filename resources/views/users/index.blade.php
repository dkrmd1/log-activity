@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Daftar User</h6>
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="ti tabler-plus me-1"></i> Tambah User
            </a>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Jabatan</th>
                    <th>Tanda Tangan</th>
                    <th class="text-end">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge bg-label-{{ $user->role_color }}">
                                {{ $user->role_label }}
                            </span>
                        </td>
                        <td>
                            @if($user->isAdmin())
                                <span class="text-body-secondary">-</span>
                            @elseif($user->signature)
                                <span class="badge bg-label-success">Sudah ada</span>
                            @else
                                <span class="badge bg-label-warning">Belum</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-icon btn-text-secondary" title="Edit">
                                <i class="ti tabler-edit"></i>
                            </a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus user ini?');">
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
                        <td colspan="5" class="text-center text-body-secondary py-4">Belum ada user.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
