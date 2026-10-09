# Tahap 19 — Perbaikan Cover PDF (Benar-benar Polos) + Nomor Halaman + Locale

## Apa yang diperbaiki

1. **Cover sekarang benar-benar polos** — tidak ada lagi kop dokumen/watermark/footer yang bocor ke halaman cover. Caranya: cover & isi laporan sekarang di-generate sebagai **2 PDF terpisah**, lalu digabung jadi 1 file PDF (pakai library tambahan bernama FPDI). Ini solusi yang lebih kuat dibanding coba-coba CSS kemarin yang ternyata tidak cukup untuk dompdf.
2. **Nomor halaman sekarang muncul** ("Halaman 1", "Halaman 2", dst) — pakai cara yang lebih andal (`counter(page)` bawaan CSS), bukan trik `enable_php` kemarin yang ternyata tidak jalan.
3. **Nama bulan dipaksa Bahasa Indonesia** ("Oktober 2026", bukan "October 2026") — tidak bergantung lagi ke pengaturan `.env` yang mungkin belum diset.

## 1. Install package tambahan untuk gabung PDF

```bash
composer require setasign/fpdi-fpdf
```

## 2. Salin file-file ini ke `log-activity/`

```
app/Http/Controllers/MonthlyReportController.php    (TIMPA)
resources/views/reports/pdf_cover.blade.php          (file baru)
resources/views/reports/pdf_content.blade.php        (file baru)
```

File lama `resources/views/reports/pdf.blade.php` **sudah tidak dipakai lagi** — boleh dihapus, atau dibiarkan saja (tidak akan terpanggil, aman).

## 3. Coba jalankan

Tidak perlu migrasi ulang.

```bash
php artisan serve
```

Buka menu **Laporan Bulanan** → pilih bulan yang ada datanya → **Download PDF**.

Cek:
- Halaman 1 (cover) harus **benar-benar polos**, tidak ada kop dokumen/watermark/footer
- Halaman 2 dst harus ada kop dokumen + nomor halaman yang terisi (misal "Halaman 1", "Halaman 2", dst — ini hitungan dari Kontrol Dokumen, bukan termasuk cover)
- Nama bulan harus Bahasa Indonesia

## Catatan soal nomor halaman

Beda sedikit dari template asli: nomor halamannya sekarang **cuma angka saja** ("Halaman 2"), bukan "Halaman 2 dari 11" — karena total halaman itu baru diketahui setelah semua konten selesai dirender, dan cara paling andal untuk dapat itu (`enable_php` + script PHP) ternyata tidak reliable di percobaan kemarin. Kalau "dari total halaman" itu penting banget buat Anda, kasih tau — saya coba pendekatan lain lagi.

## Kalau ada error

- Error terkait class `Fpdi` tidak ditemukan → `composer require setasign/fpdi-fpdf` di Langkah 1 belum berhasil, cek lagi
- Error permission waktu bikin file sementara → cek folder `storage/app/` bisa ditulis (biasanya otomatis bisa di XAMPP)

Coba dulu, kirim screenshot kalau hasilnya masih ada yang beda dari yang diharapkan.
