@extends('layouts.app')

@section('title', 'Tambah Permintaan')
@section('page-title', 'Tambah Permintaan')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('requests.store') }}">
                @csrf
                @include('requests._form', ['request' => null])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Simpan
                    </button>
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
