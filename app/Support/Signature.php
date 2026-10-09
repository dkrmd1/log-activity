<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penyimpanan tanda tangan di disk PRIVAT ("local" -> storage/app/private),
 * jadi file tidak bisa diakses lewat URL publik. Tampil di web/PDF lewat data URI.
 */
class Signature
{
    public const DISK = 'local';

    /**
     * Validasi & rapikan gambar tanda tangan (PNG/JPG), diperkecil maks. 600px lebar.
     * Mengembalikan [bytes, ekstensi] atau null kalau bukan gambar yang valid.
     */
    public static function normalize(string $bytes): ?array
    {
        $info = @getimagesizefromstring($bytes);

        if (! $info || ! in_array($info['mime'], ['image/png', 'image/jpeg'], true)) {
            return null;
        }

        [$w, $h] = $info;

        if ($w < 1 || $h < 1 || $w * $h > 12_000_000) {
            return null;
        }

        if (! function_exists('imagecreatefromstring')) {
            return [$bytes, $info['mime'] === 'image/png' ? 'png' : 'jpg'];
        }

        $src = @imagecreatefromstring($bytes);

        if (! $src) {
            return null;
        }

        $nw = min($w, 600);
        $nh = max(1, (int) round($h * $nw / $w));

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagepng($dst, null, 6);
        $png = ob_get_clean();

        return [$png, 'png'];
    }

    /**
     * Simpan tanda tangan baru, kembalikan path-nya.
     */
    public static function put(string $bytes, string $ext, string $folder = 'signatures'): string
    {
        $path = $folder . '/' . Str::random(32) . '.' . $ext;
        Storage::disk(self::DISK)->put($path, $bytes);

        return $path;
    }

    /**
     * Salin tanda tangan ke folder arsip laporan (snapshot), supaya laporan yang sudah
     * ditandatangani tidak ikut berubah kalau user mengganti tanda tangannya nanti.
     */
    public static function snapshot(?string $path): ?string
    {
        if (! $path || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        $copy = 'signatures/reports/' . Str::random(32) . '.' . $ext;
        Storage::disk(self::DISK)->copy($path, $copy);

        return $copy;
    }

    public static function exists(?string $path): bool
    {
        return $path && Storage::disk(self::DISK)->exists($path);
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function dataUri(?string $path): ?string
    {
        if (! self::exists($path)) {
            return null;
        }

        $raw = Storage::disk(self::DISK)->get($path);
        $info = @getimagesizefromstring($raw);

        return 'data:' . ($info['mime'] ?? 'image/png') . ';base64,' . base64_encode($raw);
    }
}
