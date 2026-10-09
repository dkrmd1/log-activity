<?php

namespace App\Enums;

enum PemohonEnum: string
{
    case BJB = 'BJB';
    case IDXSTI = 'IDXSTI';
    case InternalITGA = 'Internal Group IT & GA';
    case Lainnya = 'Lainnya';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
