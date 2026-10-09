@php
    $old = fn($field, $default = '') => old($field, $user->{$field} ?? $default);
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Nama</label>
        <input type="text" name="name" id="name" value="{{ $old('name') }}"
               class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" value="{{ $old('email') }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="role" class="form-label">Jabatan</label>
        <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
            <option value="staff" @selected($old('role', 'staff') === 'staff')>Staf Information Technology and General Affair</option>
            <option value="group_head" @selected($old('role') === 'group_head')>Group Head Information Technology and General Affair</option>
            <option value="admin" @selected($old('role') === 'admin')>Admin (kelola user, kategori &amp; pengaturan)</option>
        </select>
        <div class="form-text">
            Staf: input data &amp; mengajukan laporan bulanan. Group Head: ACC / tolak laporan bulanan.
            Admin: kelola user, kategori &amp; pengaturan aplikasi.
        </div>
        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="password" class="form-label">Password {{ isset($user) ? '(kosongkan jika tidak diganti)' : '' }}</label>
        <input type="password" name="password" id="password"
               class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
