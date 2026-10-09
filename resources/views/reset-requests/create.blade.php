@extends('layouts.app')

@section('title', 'Tambah Permohonan')
@section('page-title', 'Tambah Permohonan Reset')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('reset-requests.store') }}">
                @csrf
                @include('reset-requests._form', ['resetRequest' => null])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Simpan
                    </button>
                    <a href="{{ route('reset-requests.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
