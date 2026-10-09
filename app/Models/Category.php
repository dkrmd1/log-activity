<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Category extends Model
{
    /**
     * Daftar jenis pilihan yang bisa dikelola. key = isi kolom "type".
     */
    public const TYPES = [
        'isu' => 'Kategori Isu',
        'permintaan' => 'Kategori Permintaan',
        'backup' => 'Jenis Backup',
    ];

    protected $fillable = ['type', 'name'];

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Isu diurutkan A-Z (sama seperti sebelumnya); Permintaan & Backup mengikuti urutan input,
     * jadi item baru muncul paling bawah.
     */
    public function scopeOrdered(Builder $query, string $type): Builder
    {
        return $type === 'isu' ? $query->orderBy('name') : $query->orderBy('id');
    }

    /**
     * Daftar nama untuk isi dropdown di form.
     */
    public static function names(string $type): Collection
    {
        return static::ofType($type)->ordered($type)->pluck('name');
    }

    /**
     * Query data yang memakai kategori ini (untuk hitung pemakaian & ganti nama massal).
     */
    public function usageQuery(): Builder
    {
        return match ($this->type) {
            'isu' => Issue::where('category', $this->name),
            'permintaan' => ServiceRequest::where('category', $this->name),
            'backup' => BackupLog::where('jenis', $this->name),
        };
    }
}
