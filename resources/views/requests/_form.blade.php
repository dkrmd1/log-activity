@php
    $old = fn($field, $default = '') => old($field, $request->{$field} ?? $default);
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <label for="occurred_at" class="form-label">Tanggal Terjadi</label>
        <input type="date" name="occurred_at" id="occurred_at"
               value="{{ old('occurred_at', $request?->occurred_at?->format('Y-m-d')) }}"
               class="form-control @error('occurred_at') is-invalid @enderror">
        @error('occurred_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="category" class="form-label">Kategori Permintaan</label>
        <select name="category" id="category" class="form-select @error('category') is-invalid @enderror">
            <option value="">-- Pilih Kategori --</option>
            @foreach($kategoriOptions as $kategori)
                <option value="{{ $kategori }}" @selected($old('category') === $kategori)>{{ $kategori }}</option>
            @endforeach
        </select>
        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if(auth()->user()?->isAdmin())
            <div class="form-text"><a href="{{ route('categories.index', ['type' => 'permintaan']) }}" target="_blank">+ Tambah / kelola kategori permintaan</a></div>
        @endif
    </div>

    <div class="col-12">
        <label for="title" class="form-label">Permintaan</label>
        <input type="text" name="title" id="title" value="{{ $old('title') }}"
               class="form-control @error('title') is-invalid @enderror" placeholder="Judul singkat permintaan">
        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="requester" class="form-label">Pemohon</label>
        <select name="requester" id="requester" class="form-select @error('requester') is-invalid @enderror">
            <option value="">-- Pilih Pemohon --</option>
            @foreach($pemohonOptions as $pemohon)
                <option value="{{ $pemohon }}" @selected($old('requester') === $pemohon)>{{ $pemohon }}</option>
            @endforeach
        </select>
        @error('requester') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
            @foreach($statusOptions as $status)
                <option value="{{ $status }}" @selected($old('status', $request?->status?->value ?? 'Open') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="follow_up" class="form-label">Status Permintaan (Keterangan Tindak Lanjut)</label>
        <textarea name="follow_up" id="follow_up" rows="4"
                  class="form-control @error('follow_up') is-invalid @enderror"
                  placeholder="Contoh: Sudah ditambahkan atas nama Sales ASKAB pada OAO">{{ $old('follow_up') }}</textarea>
        @error('follow_up') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label for="completed_at" class="form-label">Tanggal Selesai</label>
        <input type="date" name="completed_at" id="completed_at"
               value="{{ old('completed_at', $request?->completed_at?->format('Y-m-d')) }}"
               class="form-control @error('completed_at') is-invalid @enderror">
        <div class="form-text">Kosongkan jika belum selesai.</div>
        @error('completed_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
