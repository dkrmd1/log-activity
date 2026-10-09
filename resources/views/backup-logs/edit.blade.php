@extends('layouts.app')

@section('title', 'Edit Log Backup')
@section('page-title', 'Edit Log Backup Data')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('backup-logs.update', $backupLog) }}">
                @csrf
                @method('PUT')
                @include('backup-logs._form', ['backupLog' => $backupLog])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Update
                    </button>
                    <a href="{{ route('backup-logs.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
