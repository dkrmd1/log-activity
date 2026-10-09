@php
    $old = fn($field, $default = '') => old($field, $backupLog->{$field} ?? $default);
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <label for="jenis" class="form-label">Jenis Backup</label>
        <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror">
            <option value="">-- Pilih jenis backup --</option>
            @foreach($jenisOptions as $jenis)
                <option value="{{ $jenis }}" @selected($old('jenis') === $jenis)>{{ $jenis }}</option>
            @endforeach
        </select>
        @error('jenis') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if(auth()->user()?->isAdmin())
            <div class="form-text"><a href="{{ route('categories.index', ['type' => 'backup']) }}" target="_blank">+ Tambah / kelola jenis backup</a></div>
        @endif
    </div>

    <div class="col-md-3">
        <label for="backup_date" class="form-label">Tanggal</label>
        <input type="date" name="backup_date" id="backup_date"
               value="{{ old('backup_date', $backupLog?->backup_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="form-control @error('backup_date') is-invalid @enderror">
        @error('backup_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label for="backup_time" class="form-label">Jam</label>
        <input type="time" name="backup_time" id="backup_time"
               value="{{ old('backup_time', $backupLog?->time_input ?? now()->format('H:i')) }}"
               class="form-control @error('backup_time') is-invalid @enderror">
        @error('backup_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label for="notes" class="form-label">Keterangan <span class="text-body-secondary">(opsional)</span></label>
        <textarea name="notes" id="notes" rows="3"
                  class="form-control @error('notes') is-invalid @enderror"
                  placeholder="Contoh: ukuran file, lokasi penyimpanan, atau catatan lain">{{ $old('notes') }}</textarea>
        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
