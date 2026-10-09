@extends('layouts.app')

@section('title', 'Detail Permintaan')
@section('page-title', 'Detail Permintaan')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">{{ $request->title }}</h6>
            <span class="badge bg-label-{{ $request->status->badgeColor() }}">{{ $request->status->value }}</span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <p class="text-body-secondary mb-1">Tanggal Terjadi</p>
                    <p class="fw-medium">{{ $request->occurred_at->translatedFormat('d F Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-body-secondary mb-1">Bulan</p>
                    <p class="fw-medium">{{ $request->month_label }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-body-secondary mb-1">Kategori Permintaan</p>
                    <p class="fw-medium">{{ $request->category }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-body-secondary mb-1">Pemohon</p>
                    <p class="fw-medium">{{ $request->requester }}</p>
                </div>
                <div class="col-12">
                    <p class="text-body-secondary mb-1">Status Permintaan (Keterangan)</p>
                    <p class="fw-medium" style="white-space: pre-line;">{{ $request->follow_up ?: '-' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-body-secondary mb-1">Tanggal Selesai</p>
                    <p class="fw-medium">{{ $request->completed_at?->translatedFormat('d F Y') ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <a href="{{ route('requests.edit', $request) }}" class="btn btn-primary">
                <i class="ti tabler-edit me-1"></i> Edit
            </a>
            <a href="{{ route('requests.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Riwayat Perubahan Status</h6>
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-0">
                @forelse($request->activityLogs as $log)
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
