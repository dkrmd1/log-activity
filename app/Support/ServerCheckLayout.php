<?php

namespace App\Support;

/**
 * Pembagi halaman untuk PDF form "Kontrol Server & Jaringan".
 *
 * dompdf tidak bisa memotong sel rowspan (huruf bagian & keterangan gabungan) di batas halaman:
 * kolom jadi berantakan. Karena itu halaman dibagi di sini: satu bagian (A, B, C, ...) dijaga tetap
 * utuh di satu halaman; kalau tidak muat, seluruh bagian pindah ke halaman berikutnya (header tabel
 * diulang). Hanya bagian yang lebih besar dari 1 halaman penuh yang dipotong per baris.
 *
 * Murni PHP biasa (tanpa Laravel) supaya mudah diuji.
 */
class ServerCheckLayout
{
    // Ruang isi tabel per halaman A4 landscape (pt), sudah dikurangi judul + header tabel + margin aman ~5%.
    public const PAGE_BODY = 465.0;

    // Ruang untuk blok "Tanggal / tanda tangan / nama" di bawah tabel halaman terakhir (pt).
    public const SIGN_BLOCK = 78.0;

    // PAGE_BODY sengaja ~30pt lebih kecil dari ruang sebenarnya (margin aman). Untuk blok tanda tangan
    // (yang tidak boleh terpotong) separuhnya boleh dipakai, supaya tidak membuat halaman tambahan tanpa perlu.
    protected const SIGN_SLACK = 15.0;

    protected const TITLE_ROW = 12.6;      // baris judul bagian
    protected const ROW_MIN = 15.5;        // baris 1 baris teks (sudah termasuk Paraf setinggi 12pt)
    protected const PAD = 2.8;             // padding atas+bawah sel
    protected const ITEM_LINE = 8.4;       // tinggi 1 baris teks nama item
    protected const NOTE_LINE = 8.8;       // tinggi 1 baris teks keterangan
    protected const ITEM_CHARS = 62;       // perkiraan karakter per baris di kolom Item
    protected const NOTE_CHARS = 35;       // perkiraan karakter per baris di kolom Keterangan

    /**
     * @param  iterable  $entries  baris form (urut sesuai sort_order)
     * @return array<int, array<int, array{code:string,title:string,merge:bool,rows:array,continued:bool}>>
     *         halaman => daftar blok bagian
     */
    public static function pages(iterable $entries): array
    {
        // Kelompokkan per bagian, urutan kemunculan dipertahankan.
        $sections = [];
        foreach ($entries as $e) {
            $sections[$e->section_code][] = $e;
        }

        $pages = [[]];
        $remaining = self::PAGE_BODY;

        foreach ($sections as $code => $rows) {
            $first = $rows[0];
            $merge = (bool) $first->merge_notes;
            $heights = array_map(fn ($e) => self::rowHeight($e, $merge), $rows);

            $noteNeed = $merge ? self::noteHeight($first) : 0.0;
            $total = self::TITLE_ROW + max(array_sum($heights), $noteNeed);

            // Muat di sisa halaman ini.
            if ($total <= $remaining) {
                $pages[count($pages) - 1][] = self::block($code, $first, $merge, $rows, false);
                $remaining -= $total;
                continue;
            }

            // Tidak muat, tapi muat di 1 halaman penuh -> pindah utuh ke halaman berikutnya.
            if ($total <= self::PAGE_BODY) {
                $pages[] = [self::block($code, $first, $merge, $rows, false)];
                $remaining = self::PAGE_BODY - $total;
                continue;
            }

            // Bagian lebih besar dari 1 halaman -> potong per baris.
            $continued = false;
            $i = 0;
            $n = count($rows);

            // Kalau sisa halaman terlalu kecil untuk judul + 3 baris, mulai di halaman baru.
            if ($remaining < self::TITLE_ROW + 3 * self::ROW_MIN) {
                $pages[] = [];
                $remaining = self::PAGE_BODY;
            }

            while ($i < $n) {
                $used = self::TITLE_ROW;
                $part = [];

                while ($i < $n && ($used + $heights[$i] <= $remaining || $part === [])) {
                    $part[] = $rows[$i];
                    $used += $heights[$i];
                    $i++;
                }

                $pages[count($pages) - 1][] = self::block($code, $first, $merge, $part, $continued);
                $remaining -= $used;
                $continued = true;

                if ($i < $n) {
                    $pages[] = [];
                    $remaining = self::PAGE_BODY;
                }
            }
        }

        // Blok tanda tangan tidak muat di halaman terakhir -> beri 1 halaman tambahan (hanya judul + blok tanda tangan).
        if ($remaining + self::SIGN_SLACK < self::SIGN_BLOCK) {
            $pages[] = [];
        }

        return $pages;
    }

    protected static function block(string $code, $first, bool $merge, array $rows, bool $continued): array
    {
        return [
            'code' => $code,
            'title' => $first->section_title . ($continued ? ' (lanjutan)' : ''),
            'merge' => $merge,
            'rows' => array_values($rows),
            'continued' => $continued,
        ];
    }

    protected static function rowHeight($e, bool $merge): float
    {
        $itemLines = max(1, (int) ceil(mb_strlen((string) $e->item_name) / self::ITEM_CHARS));
        $h = max(self::ROW_MIN, self::PAD + self::ITEM_LINE * $itemLines);

        if (! $merge) {
            foreach ([1, 2, 3] as $n) {
                $h = max($h, self::PAD + self::NOTE_LINE * self::lines($e->{'n' . $n}));
            }
        }

        return $h;
    }

    /** Tinggi yang dibutuhkan keterangan gabungan (diambil dari baris pertama bagian). */
    protected static function noteHeight($first): float
    {
        $max = 1;
        foreach ([1, 2, 3] as $n) {
            $max = max($max, self::lines($first->{'n' . $n}));
        }

        return self::PAD + self::NOTE_LINE * $max;
    }

    protected static function lines(?string $text): int
    {
        if ($text === null || $text === '') {
            return 1;
        }

        $total = 0;
        foreach (preg_split('/\R/', $text) as $line) {
            $total += max(1, (int) ceil(mb_strlen($line) / self::NOTE_CHARS));
        }

        return $total;
    }
}
