<?php

namespace App\Enums;

enum JenisBackupEnum: string
{
    case DatabaseCpanel = 'Database Cpanel';
    case KonfigurasiMikrotik = 'Konfigurasi Mikrotik';
    case DatabaseIntranet = 'Database Aplikasi Intranet';
    case DatabaseNas = 'Database NAS';
    case DatabaseBofis = 'Database BOFIS';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
