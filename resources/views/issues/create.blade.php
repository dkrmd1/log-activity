@extends('layouts.app')

@section('title', 'Tambah Isu')
@section('page-title', 'Tambah Isu')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('issues.store') }}">
                @csrf
                @include('issues._form', ['issue' => null])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Simpan
                    </button>
                    <a href="{{ route('issues.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
