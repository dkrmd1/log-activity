@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $latest = auth()->user()->notifications()->limit(8)->get();
@endphp

<li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifikasi">
        <span class="position-relative">
            <i class="icon-base ti tabler-bell icon-md"></i>
            @if ($unreadCount > 0)
                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size:.6rem;">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end p-0" style="width:360px;max-width:92vw;">
        <li class="dropdown-menu-header border-bottom">
            <div class="dropdown-header d-flex align-items-center py-3">
                <h6 class="mb-0 me-auto">Notifikasi</h6>
                @if ($unreadCount > 0)
                    <span class="badge bg-label-primary me-2">{{ $unreadCount }} baru</span>
                    <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-text-secondary p-1" title="Tandai semua dibaca">
                            <i class="icon-base ti tabler-checks icon-md"></i>
                        </button>
                    </form>
                @endif
            </div>
        </li>

        <li style="max-height:360px;overflow-y:auto;">
            <ul class="list-group list-group-flush">
                @forelse ($latest as $n)
                    @php $d = $n->data; @endphp
                    <li class="list-group-item list-group-item-action {{ $n->read_at ? '' : 'bg-body-tertiary' }}">
                        <a href="{{ route('notifications.read', $n->id) }}" class="d-flex gap-3 text-reset text-decoration-none">
                            <div class="flex-shrink-0">
                                <span class="avatar-initial rounded-circle bg-label-{{ $d['color'] ?? 'primary' }} d-inline-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                    <i class="icon-base ti {{ $d['icon'] ?? 'tabler-bell' }} icon-md"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small">{{ $d['title'] ?? 'Notifikasi' }}</h6>
                                <p class="mb-1 small">{{ $d['message'] ?? '' }}</p>
                                <small class="text-body-secondary">{{ $n->created_at->locale('id')->diffForHumans() }}</small>
                            </div>
                            @unless ($n->read_at)
                                <span class="badge badge-dot bg-primary align-self-center"></span>
                            @endunless
                        </a>
                    </li>
                @empty
                    <li class="list-group-item text-center text-body-secondary py-4">Belum ada notifikasi.</li>
                @endforelse
            </ul>
        </li>

        <li class="border-top">
            <div class="d-grid p-3">
                <a class="btn btn-sm btn-primary" href="{{ route('notifications.index') }}">Lihat semua notifikasi</a>
            </div>
        </li>
    </ul>
</li>
