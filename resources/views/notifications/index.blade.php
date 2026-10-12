@extends('layouts.app')

@section('page-title', 'Notifikasi')

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Semua Notifikasi</h5>
        @if (auth()->user()->unreadNotifications()->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="icon-base ti tabler-checks me-1"></i> Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <ul class="list-group list-group-flush">
        @forelse ($notifications as $n)
            @php $d = $n->data; @endphp
            <li class="list-group-item {{ $n->read_at ? '' : 'bg-body-tertiary' }}">
                <a href="{{ route('notifications.read', $n->id) }}" class="d-flex gap-3 text-reset text-decoration-none align-items-start">
                    <span class="avatar-initial rounded-circle bg-label-{{ $d['color'] ?? 'primary' }} d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;">
                        <i class="icon-base ti {{ $d['icon'] ?? 'tabler-bell' }} icon-md"></i>
                    </span>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $d['title'] ?? 'Notifikasi' }}</div>
                        <div>{{ $d['message'] ?? '' }}</div>
                        <small class="text-body-secondary">
                            {{ $n->created_at->locale('id')->translatedFormat('d F Y H:i') }}
                            · {{ $n->created_at->locale('id')->diffForHumans() }}
                        </small>
                    </div>
                    @unless ($n->read_at)
                        <span class="badge bg-primary align-self-center">Baru</span>
                    @endunless
                </a>
            </li>
        @empty
            <li class="list-group-item text-center text-body-secondary py-5">Belum ada notifikasi.</li>
        @endforelse
    </ul>

    @if ($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
