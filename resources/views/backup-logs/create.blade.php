@extends('layouts.app')

@section('title', 'Tambah Log Backup')
@section('page-title', 'Tambah Log Backup Data')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('backup-logs.store') }}">
                @csrf
                @include('backup-logs._form', ['backupLog' => null])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Simpan
                    </button>
                    <a href="{{ route('backup-logs.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
