<?php

namespace App\Enums;

enum KategoriPermintaanEnum: string
{
    case PenambahanData = 'Penambahan Data';
    case PerubahanKonfigurasi = 'Perubahan Konfigurasi';
    case AksesKodeSales = 'Akses/Kode Sales';
    case PerbaikanFitur = 'Perbaikan Fitur';
    case Lainnya = 'Lainnya';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}
