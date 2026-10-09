<?php

namespace App\Enums;

enum KategoriIsuEnum: string
{
    case SistemAplikasi = 'Sistem/Aplikasi';
    case DataSDI = 'Data/SDI';
    case ApprovalReject = 'Approval/Reject';
    case JaringanInfra = 'Jaringan/Infra';
    case Lainnya = 'Lainnya';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
