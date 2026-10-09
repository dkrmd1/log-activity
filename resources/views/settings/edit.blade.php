@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')
@section('page-title', 'Pengaturan Aplikasi')

@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Logo, Logo Laporan PDF, Favicon & Nama Aplikasi</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Logo --}}
                        <div class="d-flex align-items-center mb-4">
                            <div class="border rounded p-2" style="width:96px;height:96px;display:flex;align-items:center;justify-content:center;">
                                <img id="logoPreview"
                                     src="{{ $setting->logo_url ?? '' }}"
                                     alt="Logo"
                                     style="max-width:100%;max-height:100%;{{ $setting->logo_url ? '' : 'display:none;' }}">
                                <i id="logoPlaceholder" class="ti tabler-photo icon-lg text-body-secondary" style="{{ $setting->logo_url ? 'display:none;' : '' }}"></i>
                            </div>
                            <div class="ms-4">
                                <label for="logo" class="btn btn-outline-primary btn-sm mb-1">
                                    <i class="ti tabler-upload me-1"></i> Upload Logo
                                </label>
                                <input type="file" name="logo" id="logo" accept="image/*" class="d-none">
                                <div class="form-text">Tampil di sidebar & halaman login. PNG/JPG, maksimal 1MB.</div>
                                @error('logo') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Logo Laporan PDF --}}
                        <div class="d-flex align-items-center mb-4">
                            <div class="border rounded p-2" style="width:96px;height:96px;display:flex;align-items:center;justify-content:center;">
                                <img id="reportLogoPreview"
                                     src="{{ $setting->report_logo_url ?? '' }}"
                                     alt="Logo Laporan PDF"
                                     style="max-width:100%;max-height:100%;{{ $setting->report_logo_url ? '' : 'display:none;' }}">
                                <i id="reportLogoPlaceholder" class="ti tabler-file-type-pdf icon-lg text-body-secondary" style="{{ $setting->report_logo_url ? 'display:none;' : '' }}"></i>
                            </div>
                            <div class="ms-4">
                                <label for="report_logo" class="btn btn-outline-primary btn-sm mb-1">
                                    <i class="ti tabler-upload me-1"></i> Upload Logo Laporan PDF
                                </label>
                                <input type="file" name="report_logo" id="report_logo" accept=".png,.jpg,.jpeg" class="d-none">
                                <div class="form-text">Khusus untuk PDF Laporan Bulanan (header & cover), terpisah dari logo website. PNG/JPG, maksimal 1MB.</div>
                                @error('report_logo') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        {{-- Favicon --}}
                        <div class="d-flex align-items-center mb-4">
                            <div class="border rounded p-2" style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                                <img id="faviconPreview"
                                     src="{{ $setting->favicon_url ?? '' }}"
                                     alt="Favicon"
                                     style="max-width:100%;max-height:100%;{{ $setting->favicon_url ? '' : 'display:none;' }}">
                                <i id="faviconPlaceholder" class="ti tabler-browser text-body-secondary" style="{{ $setting->favicon_url ? 'display:none;' : '' }}"></i>
                            </div>
                            <div class="ms-4">
                                <label for="favicon" class="btn btn-outline-primary btn-sm mb-1">
                                    <i class="ti tabler-upload me-1"></i> Upload Favicon
                                </label>
                                <input type="file" name="favicon" id="favicon" accept=".ico,.png,.jpg,.jpeg,.svg" class="d-none">
                                <div class="form-text">Muncul di tab browser. Idealnya gambar persegi (mis. 32x32 atau 64x64px), format .ico/.png, maksimal 512KB.</div>
                                @error('favicon') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <label for="app_name" class="form-label">Nama Aplikasi</label>
                            <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $setting->app_name) }}"
                                   class="form-control @error('app_name') is-invalid @enderror">
                            <div class="form-text">Muncul di judul tab browser dan sapaan halaman login.</div>
                            @error('app_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti tabler-device-floppy me-1"></i> Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-2">Pratinjau di Sidebar</h6>
                    <div class="d-flex align-items-center border rounded p-3 mb-4">
                        @if($setting->logo_url)
                            <img src="{{ $setting->logo_url }}" alt="Logo" style="height:28px;width:auto;">
                        @else
                            <i class="ti tabler-clipboard-text icon-lg text-primary"></i>
                        @endif
                    </div>

                    <h6 class="mb-2">Pratinjau Tab Browser</h6>
                    <div class="d-flex align-items-center border rounded p-2 gap-2" style="background:#f4f4f6;">
                        <div style="width:16px;height:16px;flex-shrink:0;">
                            @if($setting->favicon_url)
                                <img src="{{ $setting->favicon_url }}" alt="Favicon" style="width:100%;height:100%;object-fit:contain;">
                            @else
                                <i class="ti tabler-clipboard-text" style="font-size:16px;"></i>
                            @endif
                        </div>
                        <small class="text-truncate">{{ $setting->app_name }}</small>
                    </div>

                    <p class="text-body-secondary small mt-3 mb-0">
                        Kalau belum upload logo/favicon, sistem tetap menampilkan ikon default supaya tidak kosong.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('logo').addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            const url = URL.createObjectURL(this.files[0]);
            document.getElementById('logoPreview').src = url;
            document.getElementById('logoPreview').style.display = 'block';
            document.getElementById('logoPlaceholder').style.display = 'none';
        });

        document.getElementById('report_logo').addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            const url = URL.createObjectURL(this.files[0]);
            document.getElementById('reportLogoPreview').src = url;
            document.getElementById('reportLogoPreview').style.display = 'block';
            document.getElementById('reportLogoPlaceholder').style.display = 'none';
        });

        document.getElementById('favicon').addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            const url = URL.createObjectURL(this.files[0]);
            document.getElementById('faviconPreview').src = url;
            document.getElementById('faviconPreview').style.display = 'block';
            document.getElementById('faviconPlaceholder').style.display = 'none';
        });
    </script>
@endpush