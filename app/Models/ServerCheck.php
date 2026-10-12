<?php

namespace App\Models;

use App\Support\Signature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class ServerCheck extends Model
{
    protected $fillable = [
        'check_date', 'created_by',
        'signed_by', 'signed_name', 'signed_signature', 'signed_at',
        'rejected_by', 'rejected_name', 'reject_note', 'rejected_at',
    ];

    protected $casts = ['check_date' => 'date', 'signed_at' => 'datetime', 'rejected_at' => 'datetime'];

    public function entries(): HasMany
    {
        return $this->hasMany(ServerCheckEntry::class)->orderBy('sort_order')->orderBy('id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSigned(): bool
    {
        return filled($this->signed_signature);
    }

    /**
     * Form sedang ditolak Group Head (menunggu direvisi Staf).
     */
    public function isRejected(): bool
    {
        return filled($this->rejected_at) && filled($this->reject_note);
    }

    /**
     * Cabut penolakan (Staf sudah merevisi atau Group Head akhirnya menandatangani),
     * tanpa mengubah updated_at supaya tidak dianggap "data berubah" oleh Laporan Bulanan.
     */
    public function clearRejection(): void
    {
        $this->timestamps = false;
        $this->update(['rejected_by' => null, 'rejected_name' => null, 'reject_note' => null, 'rejected_at' => null]);
        $this->timestamps = true;
    }

    /**
     * Tanda tangan penanggung jawab sebagai data URI (untuk web & PDF), atau null.
     */
    public function getSignedSignatureUriAttribute(): ?string
    {
        return Signature::dataUri($this->signed_signature, true);
    }

    /**
     * Isi blok "Tanggal / tanda tangan / nama" di kanan bawah form.
     * Blok ini HANYA diisi oleh tanda tangan Group Head (tombol "Tanda Tangani Form").
     * Kalau belum ditandatangani Group Head, blok dibiarkan kosong - tidak ada fallback ke
     * tanda tangan staf/petugas. Tanda tangan staf hanya muncul sebagai Paraf per baris.
     *
     * @return array{name: ?string, uri: ?string, signed: bool}
     */
    public function getSignoffAttribute(): array
    {
        if (! $this->isSigned()) {
            return ['name' => null, 'uri' => null, 'signed' => false];
        }

        return ['name' => $this->signed_name, 'uri' => $this->signed_signature_uri, 'signed' => true];
    }

    /**
     * Hapus tanda tangan form (file salinan + kolomnya), tanpa mengubah updated_at
     * supaya tidak dianggap "data berubah" oleh Laporan Bulanan.
     */
    public function clearSignature(): void
    {
        Signature::delete($this->signed_signature);

        $this->timestamps = false;
        $this->update(['signed_by' => null, 'signed_name' => null, 'signed_signature' => null, 'signed_at' => null]);
        $this->timestamps = true;
    }

    /**
     * Contoh: "01 September 2026" (selalu Bahasa Indonesia).
     */
    public function getDateLabelAttribute(): string
    {
        return $this->check_date->copy()->locale('id')->translatedFormat('d F Y');
    }

    public function scopeBulan(Builder $query, ?string $bulanTahun): Builder
    {
        if (! $bulanTahun) {
            return $query;
        }

        $timestamp = strtotime('1 ' . $bulanTahun);

        if ($timestamp === false) {
            return $query;
        }

        return $query
            ->whereMonth('check_date', date('n', $timestamp))
            ->whereYear('check_date', date('Y', $timestamp));
    }

    /**
     * Buat form baru untuk tanggal tertentu: salin semua item AKTIF dari master sebagai snapshot.
     */
    public static function createForDate(string $date, ?int $userId): self
    {
        $check = static::create(['check_date' => $date, 'created_by' => $userId]);

        $sort = 0;
        $rows = [];
        $now = now();

        $sections = ControlSection::with(['items' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')->orderBy('id')->get();

        foreach ($sections as $section) {
            foreach ($section->items->values() as $i => $item) {
                $rows[] = [
                    'server_check_id' => $check->id,
                    'section_code' => $section->code,
                    'section_title' => $section->title,
                    'merge_notes' => $section->merge_notes,
                    'item_no' => $i + 1,
                    'item_name' => $item->name,
                    'sort_order' => ++$sort,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        ServerCheckEntry::insert($rows);

        return $check;
    }

    /**
     * Jumlah baris yang sudah diisi per waktu pemeriksaan: [1 => n, 2 => n, 3 => n].
     */
    public function filledCounts(): array
    {
        $counts = [];

        foreach (array_keys(ServerCheckEntry::SLOTS) as $slot) {
            $counts[$slot] = $this->entries->filter(fn ($e) => filled($e->{'c' . $slot}))->count();
        }

        return $counts;
    }

    /**
     * Status 1 waktu pemeriksaan untuk tabel di Laporan Bulanan:
     *   "OK"                 semua item terisi dan semuanya OK
     *   "NOK"                ada minimal 1 item NOK
     *   "Sebagian (18/21)"   belum semua item terisi (yang terisi OK)
     *   "-"                  belum diperiksa sama sekali
     */
    public function slotStatus(int $slot): string
    {
        $total = $this->entries->count();
        $values = $this->entries->pluck('c' . $slot)->filter(fn ($v) => filled($v));

        if ($values->isEmpty()) {
            return '-';
        }

        if ($values->contains('NOK')) {
            return 'NOK';
        }

        return $values->count() >= $total ? 'OK' : "Sebagian ({$values->count()}/{$total})";
    }

    /**
     * Peta user_id => data URI tanda tangan, untuk kolom Paraf di PDF.
     * $checks harus sudah di-eager-load relasi entries.
     */
    public static function signatureMap(Collection $checks): array
    {
        $ids = $checks->flatMap(fn ($c) => $c->entries->pluck('paraf_by'))->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return User::whereIn('id', $ids)->get()
            ->mapWithKeys(fn (User $u) => [$u->id => Signature::dataUri($u->signature, true)])
            ->filter()
            ->all();
    }
}
