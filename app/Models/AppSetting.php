<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AppSetting extends Model
{
    protected $fillable = [
        'app_name',
        'logo',
        'favicon',
        'report_logo',
    ];

    /**
     * Pengaturan aplikasi cuma 1 baris (id = 1). Kalau belum ada, otomatis dibuatkan default-nya.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['app_name' => 'Log Activity IT & GA']
        );
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function getReportLogoUrlAttribute(): ?string
    {
        return $this->report_logo ? Storage::url($this->report_logo) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? Storage::url($this->favicon) : null;
    }
}