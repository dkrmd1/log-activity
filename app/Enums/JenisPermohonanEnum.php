<?php

namespace App\Enums;

enum JenisPermohonanEnum: string
{
    case ResetPin = 'Reset PIN';
    case Unblock = 'Unblock';
    case ResetPassword = 'Reset Password';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
