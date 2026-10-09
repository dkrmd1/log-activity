<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ControlSection extends Model
{
    protected $fillable = ['code', 'title', 'merge_notes', 'sort_order'];

    protected $casts = ['merge_notes' => 'boolean'];

    public function items(): HasMany
    {
        return $this->hasMany(ControlItem::class, 'section_id')->orderBy('sort_order')->orderBy('id');
    }
}
