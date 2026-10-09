@extends('layouts.app')

@section('title', 'Detail Permohonan')
@section('page-title', 'Detail Permohonan Reset')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ $resetRequest->name }} ({{ $resetRequest->account_user_id }})</h6>
            <span class="badge bg-label-{{ $resetRequest->status->badgeColor() }}">{{ $resetRequest->status->value }}</span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <p class="text-body-secondary mb-1">Tanggal Permohonan</p>
                    <p class="fw-medium">{{ $resetRequest->requested_at->translatedFormat('d F Y') }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-body-secondary mb-1">User ID</p>
                    <p class="fw-medium">{{ $resetRequest->account_user_id }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-body-secondary mb-1">Email</p>
                    <p class="fw-medium">{{ $resetRequest->email }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-body-secondary mb-1">Nama</p>
                    <p class="fw-medium">{{ $resetRequest->name }}</p>
                </div>
                <div class="col-12">
                    <p class="text-body-secondary mb-1">Jenis Permohonan</p>
                    <p class="fw-medium">
                        @foreach($resetRequest->types as $type)
                            <span class="badge bg-label-info me-1">{{ $type }}</span>
                        @endforeach
                    </p>
                </div>
                <div class="col-12">
                    <p class="text-body-secondary mb-1">Keterangan</p>
                    <p class="fw-medium" style="white-space: pre-line;">{{ $resetRequest->notes ?: '-' }}</p>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <a href="{{ route('reset-requests.edit', $resetRequest) }}" class="btn btn-primary">
                <i class="ti tabler-edit me-1"></i> Edit
            </a>
            <a href="{{ route('reset-requests.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Riwayat Perubahan Status</h6>
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @forelse($resetRequest->activityLogs as $log)
                    <li class="d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <span class="avatar-initial rounded-circle bg-label-primary me-3" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="ti tabler-history icon-sm"></i>
                        </span>
                        <div>
                            <div>
                                @if($log->old_status)
                                    <span class="badge bg-label-secondary">{{ $log->old_status }}</span>
                                    <i class="ti tabler-arrow-right mx-1"></i>
                                @else
                                    <span class="text-body-secondary me-1">Dibuat dengan status</span>
                                @endif
                                <span class="badge bg-label-{{ \App\Enums\StatusEnum::from($log->new_status)->badgeColor() }}">
                                    {{ $log->new_status }}
                                </span>
                            </div>
                            <div class="text-body-secondary small mt-1">
                                {{ $log->user->name ?? 'Sistem' }} &middot; {{ $log->created_at->translatedFormat('d F Y, H:i') }} WIB
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="text-body-secondary">Belum ada riwayat perubahan.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endsection
