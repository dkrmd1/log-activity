@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')

    <div class="row g-4">
        {{-- Foto & Info Profil --}}
        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Info Profil</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="d-flex align-items-center mb-4">
                            <img id="avatarPreview"
                                 src="{{ $user->avatar_url ?? asset('assets/img/avatars/1.png') }}"
                                 alt="Foto profil" class="rounded-circle" style="width:84px;height:84px;object-fit:cover;">
                            <div class="ms-4">
                                <label for="avatar" class="btn btn-outline-primary btn-sm mb-1">
                                    <i class="ti tabler-camera me-1"></i> Ganti Foto
                                </label>
                                <input type="file" name="avatar" id="avatar" accept="image/*" class="d-none"
                                       onchange="document.getElementById('avatarPreview').src = URL.createObjectURL(this.files[0])">
                                <div class="form-text">JPG/PNG, maksimal 2MB.</div>
                                @error('avatar') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                                       class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-device-floppy me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tanda Tangan (Admin tidak menandatangani laporan, jadi kartu ini disembunyikan) --}}
            @unless($user->isAdmin())
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Tanda Tangan</h6>
                </div>
                <div class="card-body">
                    <p class="text-body-secondary small">
                        Dipakai di Laporan Bulanan
                        @if($user->isGroupHead())
                            (kolom <strong>Diketahui oleh</strong>) saat Anda menyetujui laporan.
                        @else
                            (kolom <strong>Dibuat oleh</strong>) saat Anda mengajukan laporan ke Group Head.
                        @endif
                    </p>

                    @if($signatureUri)
                        <div class="mb-3">
                            <div class="small text-body-secondary mb-1">Tanda tangan tersimpan saat ini:</div>
                            <div class="border rounded p-2 bg-white d-inline-block">
                                <img src="{{ $signatureUri }}" alt="Tanda tangan" style="max-height:90px; max-width:100%;">
                            </div>
                            <form method="POST" action="{{ route('profile.signature.destroy') }}" class="d-inline ms-2"
                                  onsubmit="return confirm('Hapus tanda tangan tersimpan?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </div>
                    @else
                        <div class="alert alert-warning py-2">Anda belum punya tanda tangan tersimpan.</div>
                    @endif

                    <form method="POST" action="{{ route('profile.signature') }}" enctype="multipart/form-data" id="signatureForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="signature_data" id="signatureData">

                        <label class="form-label">Gambar tanda tangan di kotak ini (mouse / jari / pen)</label>
                        <div>
                            <canvas id="sigPad" width="600" height="200"
                                    style="width:100%; max-width:600px; height:auto; aspect-ratio:3/1; background:#fff; border:1px dashed #9a9ab0; border-radius:6px; touch-action:none; cursor:crosshair;"></canvas>
                        </div>
                        <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="sigClear">
                                <i class="ti tabler-eraser me-1"></i> Hapus Coretan
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" id="sigSave">
                                <i class="ti tabler-device-floppy me-1"></i> Simpan Tanda Tangan
                            </button>
                        </div>

                        <div class="mt-4">
                            <label for="signature_file" class="form-label">Atau upload file tanda tangan <span class="text-body-secondary">(PNG/JPG, maks 1MB, sebaiknya latar putih/transparan)</span></label>
                            <input type="file" name="signature_file" id="signature_file" accept="image/png,image/jpeg" class="form-control">
                            <div class="form-text">Kalau memilih file, gambar di kotak di atas diabaikan.</div>
                        </div>

                        @error('signature_data') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        @error('signature_file') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </form>
                </div>
            </div>

            @endunless

            {{-- Ganti Password --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Ganti Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-12">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti tabler-lock me-1"></i> Ganti Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Ringkasan Akun --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $user->avatar_url ?? asset('assets/img/avatars/1.png') }}"
                         alt="Foto profil" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover;">
                    <h6 class="mb-0">{{ $user->name }}</h6>
                    <p class="text-body-secondary mb-2">{{ $user->email }}</p>
                    <span class="badge bg-label-{{ $user->role_color }}">
                        {{ $user->role_label }}
                    </span>
                    <p class="text-body-secondary small mt-2 mb-0">{{ $user->position_label }}</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var canvas = document.getElementById('sigPad');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    ctx.lineWidth = 2.6;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#111111';

    var drawing = false, hasInk = false;

    function pos(e) {
        var r = canvas.getBoundingClientRect();
        return { x: (e.clientX - r.left) * canvas.width / r.width, y: (e.clientY - r.top) * canvas.height / r.height };
    }

    canvas.addEventListener('pointerdown', function (e) {
        drawing = true;
        hasInk = true;
        canvas.setPointerCapture(e.pointerId);
        var p = pos(e);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineTo(p.x + 0.01, p.y);
        ctx.stroke();
        e.preventDefault();
    });
    canvas.addEventListener('pointermove', function (e) {
        if (!drawing) return;
        var p = pos(e);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
        e.preventDefault();
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (ev) {
        canvas.addEventListener(ev, function () { drawing = false; });
    });

    document.getElementById('sigClear').addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasInk = false;
    });

    document.getElementById('signatureForm').addEventListener('submit', function (e) {
        var fileInput = document.getElementById('signature_file');
        var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

        if (hasFile) {
            document.getElementById('signatureData').value = '';
            return;
        }
        if (!hasInk) {
            e.preventDefault();
            alert('Gambar tanda tangan dulu di kotak, atau pilih file gambar.');
            return;
        }
        // Canvas dibiarkan transparan; latar putih hanya tampilan CSS.
        document.getElementById('signatureData').value = canvas.toDataURL('image/png');
    });
})();
</script>
@endpush
