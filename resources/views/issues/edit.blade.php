@extends('layouts.app')

@section('title', 'Edit Isu')
@section('page-title', 'Edit Isu')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('issues.update', $issue) }}">
                @csrf
                @method('PUT')
                @include('issues._form', ['issue' => $issue])

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti tabler-device-floppy me-1"></i> Update
                    </button>
                    <a href="{{ route('issues.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
