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

        // Buang margin kosong di sekeliling coretan supaya tanda tangan memenuhi gambar.
        return [self::trim($png), 'png'];
    }

    /**
     * Potong margin transparan/putih di sekeliling coretan (+ sedikit padding), supaya saat diperkecil
     * jadi Paraf tanda tangan tetap terbaca. Rasio lebar:tinggi dibatasi $maxAspect agar tidak melebar
     * keluar kolom Paraf. Mengembalikan PNG; kalau tidak bisa diproses, bytes asli dikembalikan.
     */
    public static function trim(string $bytes, int $pad = 3, float $maxAspect = 5.0): string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $bytes;
        }

        $src = @imagecreatefromstring($bytes);

        if (! $src) {
            return $bytes;
        }

        imagepalettetotruecolor($src);

        $w = imagesx($src);
        $h = imagesy($src);
        [$minX, $minY, $maxX, $maxY] = [$w, $h, -1, -1];

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $c = imagecolorat($src, $x, $y);

                if ((($c >> 24) & 0x7F) > 100) {
                    continue; // transparan
                }

                if ((($c >> 16) & 0xFF) > 235 && (($c >> 8) & 0xFF) > 235 && ($c & 0xFF) > 235) {
                    continue; // putih (latar)
                }

                $minX = min($minX, $x);
                $maxX = max($maxX, $x);
                $minY = min($minY, $y);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < 0) {
            return $bytes; // kosong, biarkan apa adanya
        }

        $x0 = max(0, $minX - $pad);
        $y0 = max(0, $minY - $pad);
        $cw = min($w, $maxX + $pad + 1) - $x0;
        $ch = min($h, $maxY + $pad + 1) - $y0;

        $dw = $cw;
        $dh = max($ch, (int) ceil($cw / $maxAspect));

        $dst = imagecreatetruecolor($dw, $dh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopy($dst, $src, 0, (int) (($dh - $ch) / 2), $x0, $y0, $cw, $ch);

        ob_start();
        imagepng($dst, null, 6);

        return ob_get_clean();
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

    /**
     * @param  bool  $trim  true = potong margin kosong dulu (dipakai untuk Paraf & tanda tangan di PDF,
     *                      termasuk tanda tangan lama yang sudah tersimpan dengan margin besar)
     */
    public static function dataUri(?string $path, bool $trim = false): ?string
    {
        if (! self::exists($path)) {
            return null;
        }

        $raw = Storage::disk(self::DISK)->get($path);

        if ($trim) {
            $raw = self::trim($raw);
        }

        $info = @getimagesizefromstring($raw);

        return 'data:' . ($info['mime'] ?? 'image/png') . ';base64,' . base64_encode($raw);
    }
}
