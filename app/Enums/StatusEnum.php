<?php

namespace App\Enums;

enum StatusEnum: string
{
    case Open = 'Open';
    case InProgress = 'In Progress';
    case Done = 'Done';
    case Cancelled = 'Cancelled';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    /**
     * Warna badge Bootstrap, dipakai nanti di tampilan tabel (Tahap 3/4).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Open => 'danger',
            self::InProgress => 'warning',
            self::Done => 'success',
            self::Cancelled => 'secondary',
        };
    }
}
