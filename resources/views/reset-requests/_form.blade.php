@php
    $old = fn($field, $default = '') => old($field, $resetRequest->{$field} ?? $default);
    $selectedTypes = old('types', $resetRequest->types ?? []);
@endphp

<div class="row g-4">
    <div class="col-md-4">
        <label for="requested_at" class="form-label">Tanggal Permohonan</label>
        <input type="date" name="requested_at" id="requested_at"
               value="{{ old('requested_at', $resetRequest?->requested_at?->format('Y-m-d')) }}"
               class="form-control @error('requested_at') is-invalid @enderror">
        @error('requested_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="account_user_id" class="form-label">User ID</label>
        <input type="text" name="account_user_id" id="account_user_id" value="{{ $old('account_user_id') }}"
               class="form-control @error('account_user_id') is-invalid @enderror" placeholder="Contoh: AB1234">
        @error('account_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
            @foreach($statusOptions as $status)
                <option value="{{ $status }}" @selected($old('status', $resetRequest?->status?->value ?? 'Open') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

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

    <div class="col-12">
        <label class="form-label d-block">Jenis Permohonan <span class="text-body-secondary">(boleh pilih lebih dari satu)</span></label>
        <div class="d-flex flex-wrap gap-4">
            @foreach($typeOptions as $type)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="types[]" value="{{ $type }}"
                           id="type-{{ \Illuminate\Support\Str::slug($type) }}"
                           @checked(in_array($type, $selectedTypes))>
                    <label class="form-check-label" for="type-{{ \Illuminate\Support\Str::slug($type) }}">
                        {{ $type }}
                    </label>
                </div>
            @endforeach
        </div>
        @error('types') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="notes" class="form-label">Keterangan <span class="text-body-secondary">(opsional)</span></label>
        <textarea name="notes" id="notes" rows="3"
                  class="form-control @error('notes') is-invalid @enderror"
                  placeholder="Catatan tambahan dari CS, kalau ada">{{ $old('notes') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
