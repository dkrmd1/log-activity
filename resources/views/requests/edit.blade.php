@extends('layouts.app')

@section('title', 'Edit Permintaan')
@section('page-title', 'Edit Permintaan')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('requests.update', $request) }}">
                @csrf
                @method('PUT')
                @include('requests._form', ['request' => $request])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Update
                    </button>
                    <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
