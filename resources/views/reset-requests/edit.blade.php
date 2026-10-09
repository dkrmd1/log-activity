@extends('layouts.app')

@section('title', 'Edit Permohonan')
@section('page-title', 'Edit Permohonan Reset')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('reset-requests.update', $resetRequest) }}">
                @csrf
                @method('PUT')
                @include('reset-requests._form', ['resetRequest' => $resetRequest])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Update
                    </button>
                    <a href="{{ route('reset-requests.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
