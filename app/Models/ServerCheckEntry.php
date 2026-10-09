<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerCheckEntry extends Model
{
    /**
     * Waktu pemeriksaan: nomor slot => label di header tabel (persis seperti form asli).
     */
    public const SLOTS = [
        1 => '08 : 00',
        2 => '12:00',
        3 => '16:30',
    ];

    public const CONDITIONS = ['OK', 'NOK'];

    protected $fillable = [
        'server_check_id', 'section_code', 'section_title', 'merge_notes', 'item_no', 'item_name', 'sort_order',
        'c1', 'n1', 'c2', 'n2', 'c3', 'n3', 'paraf_by',
    ];

    protected $casts = ['merge_notes' => 'boolean'];

    public function serverCheck(): BelongsTo
    {
        return $this->belongsTo(ServerCheck::class);
    }

    public function parafUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paraf_by');
    }
}
