# Paket Fitur — Log Backup, Kelola Kategori, Role Group, Persetujuan Laporan

## A. Role (3 role terpisah)
| Role di DB | Jabatan | Hak |
|---|---|---|
| `admin` | Admin | Kelola **user, kategori, pengaturan aplikasi** + semua input/lihat data. Tidak mengajukan / meng-ACC laporan, tidak punya tanda tangan |
| `group_head` | Group Head Information Technology and General Affair | Lihat/input data + **ACC / tolak** laporan bulanan, bertanda tangan ("Diketahui oleh") |
| `staff` | Staf Information Technology and General Affair | Input data + **mengajukan** laporan bulanan, bertanda tangan ("Dibuat oleh") |

Akun contoh dari seeder: `admin@bjbsekuritas.co.id`, `gh@bjbsekuritas.co.id` (baru), `staff@bjbsekuritas.co.id`.

**Kalau Anda sempat menjalankan migration versi sebelumnya** (yang mengubah semua `admin` menjadi `group_head`),
kembalikan akun admin dengan:
```bash
php artisan tinker
>>> App\Models\User::where('email','admin@bjbsekuritas.co.id')->update(['role'=>'admin']);
```
(ganti email sesuai akun admin Anda). Setelah itu role bisa diatur normal dari Manajemen User.

## B. Tanda tangan
Tiap user mengisi tanda tangan sekali di **Profil Saya → Tanda Tangan**:
digambar langsung di kotak (mouse/jari/pen) **atau** upload file PNG/JPG.
File disimpan di folder privat (`storage/app/private/signatures`), tidak bisa dibuka lewat URL publik.

## C. Alur persetujuan Laporan Bulanan
1. **Staf** buka **Laporan Bulanan**, pilih bulan, klik **Ajukan ke Group Head**
   → tanda tangan Staf masuk ke kolom **Dibuat oleh**.
2. **Group Head** melihat badge angka merah di menu Laporan Bulanan, lalu:
   - **Setujui (ACC)** → tanda tangan GH masuk ke kolom **Diketahui oleh**, PDF jadi final; atau
   - **Tolak / Minta Revisi** (wajib isi catatan) → Staf memperbaiki lalu **Ajukan Ulang**.
   **Lihat Laporan** (tombol di halaman Laporan Bulanan): pratinjau laporan langsung di browser tanpa download PDF dulu,
   tersedia untuk semua role, selalu menampilkan data & status terbaru. Cocok dipakai Group Head sebelum ACC/tolak.
3. PDF: blok "Dibuat oleh / Diketahui oleh" ada di halaman **Kontrol Dokumen**.
   - Belum di-ACC → watermark **DRAFT**; sudah di-ACC → **CONFIDENTIAL**.
4. **Pengaman data:** kalau setelah diajukan/di-ACC ada Log Isu/Permintaan/Reset/Backup bulan itu yang
   ditambah/diedit/dihapus, tanda tangan otomatis tidak berlaku (watermark DRAFT) dan Staf harus mengajukan ulang.
   Group Head tidak bisa meng-ACC laporan yang datanya sudah berubah.

Aturan: hanya Staf yang bisa mengajukan; hanya Group Head yang bisa ACC/tolak; periode tanpa data tidak bisa diajukan.

## D. Fitur sebelumnya (tetap ada)
- **Di Laporan Bulanan** section **Backup Data Summary** berupa tabel ringkas: satu baris per **tanggal**, satu kolom per **jenis backup**,
  isi sel = jam backup (contoh `17.00`; kalau 2x sehari `08.00, 17.00`; `-` = tidak ada), plus baris **Jumlah** per jenis.
  Jumlah baris maksimal sebanyak hari dalam sebulan, tidak bertambah per catatan. Rincian lengkap tetap ada di menu Log Backup & exportnya.
- **Log Backup Data** (menu Log Activity) + section "Backup Data Summary" di laporan bulanan.
- **Kelola Kategori** (Administrasi): Kategori Isu / Permintaan / Jenis Backup bisa ditambah dari browser.

## E. Kontrol Server & Jaringan (form harian + PDF landscape)
Menu **Log Activity -> Kontrol Server**. Satu form per tanggal, pemeriksaan pukul 08:00, 12:00 dan 16:30
(Kondisi OK/NOK + Keterangan per item). Bagian & item awal (A: Switch/Router/Internet 18 item, B: UPS, C: Kelistrikan & AC)
diambil dari form asli.
- **Isi form:** buat/buka form per tanggal -> isi -> Simpan. Tombol "Semua OK" per waktu mempercepat pengisian.
- **Paraf** terisi otomatis dari tanda tangan user yang terakhir mengisi baris itu (kalau belum punya tanda tangan, tampil nama).
- **Lihat** = pratinjau PDF langsung di browser. **PDF** = download, **A4 landscape**, tampilan seperti form asli.
- **Laporan Bulanan:** muncul section baru **Monitoring Ruang Server Summary <bulan tahun>** (portrait, seperti section lain):
  tabel per tanggal dengan status tiap jam pemeriksaan (08:00 / 12:00 / 16:30), contoh `OK  OK  OK`.
  Status: `OK` = semua item terisi & normal, `NOK` = ada item bermasalah, `Sebagian (18/21)` = belum semua item terisi, `-` = belum diperiksa.
  Section dilewati kalau bulan itu tidak ada form. Perubahan form bulan itu setelah laporan diajukan/di-ACC
  otomatis membatalkan tanda tangan (sama seperti data lain) sampai diajukan ulang.
  (PDF form harian per tanggal tetap A4 landscape seperti form asli.)
- **Admin:** menu **Administrasi -> Item Kontrol Server** untuk menambah/ubah/nonaktifkan bagian & item (mis. bagian D, E).
  Perubahan hanya berlaku untuk form BARU; form lama tidak berubah.
- Satu bagian tidak dipotong antar-halaman; bagian yang lebih besar dari 1 halaman dipotong per baris dengan judul "(lanjutan)".
- Warna header ada di bagian `<style>` file `resources/views/server-checks/pdf.blade.php` (merah `#FF4B4B`, kuning `#FFFF00`).

## File BARU
```
app/Support/Signature.php
app/Support/ServerCheckLayout.php
app/Models/ControlSection.php
app/Models/ControlItem.php
app/Models/ServerCheck.php
app/Models/ServerCheckEntry.php
app/Http/Controllers/ServerCheckController.php
app/Http/Controllers/ControlItemController.php
database/migrations/2026_01_01_000015_create_server_check_tables.php
resources/views/server-checks/*.blade.php     (index, edit, pdf, _preview_modal)
resources/views/control-items/index.blade.php
app/Models/BackupLog.php
app/Models/Category.php
app/Models/MonthlyReport.php
app/Http/Controllers/BackupLogController.php
app/Http/Controllers/CategoryController.php
app/Http/Controllers/ReportApprovalController.php
app/Http/Requests/BackupLogRequest.php
app/Http/Requests/CategoryRequest.php
app/Exports/BackupLogsExport.php
database/migrations/2026_01_01_000011_create_backup_logs_table.php
database/migrations/2026_01_01_000012_create_categories_table.php
database/migrations/2026_01_01_000013_update_roles_and_add_signature_to_users_table.php
database/migrations/2026_01_01_000014_create_monthly_reports_table.php
resources/views/backup-logs/*.blade.php        (5 file)
resources/views/categories/index.blade.php
```

## File yang DIUBAH (timpa)
```
routes/web.php
database/seeders/DatabaseSeeder.php
app/Models/User.php
app/Models/MonthlyReport.php   (sidik jari data + Kontrol Server)
app/Http/Middleware/EnsureUserIsAdmin.php
app/Http/Controllers/IssueController.php
app/Http/Controllers/RequestController.php
app/Http/Controllers/MonthlyReportController.php
app/Http/Controllers/ProfileController.php
app/Http/Requests/ServiceRequestRequest.php
app/Http/Requests/UserRequest.php
resources/views/layouts/app.blade.php
resources/views/issues/_form.blade.php
resources/views/requests/_form.blade.php
resources/views/users/_form.blade.php
resources/views/users/index.blade.php
resources/views/profile/edit.blade.php
resources/views/reports/index.blade.php
resources/views/reports/pdf_content.blade.php
```

## Jalankan
```bash
php artisan migrate
php artisan serve
```
Langkah pertama setelah login: **Staf dan Group Head isi tanda tangan di Profil Saya** (Admin tidak perlu).
(Halaman Manajemen User punya kolom "Tanda Tangan" untuk memantau siapa yang belum mengisi.)

## File lama yang sudah tidak dipakai (boleh dihapus)
```
app/Http/Controllers/IssueCategoryController.php
app/Http/Requests/IssueCategoryRequest.php
app/Models/IssueCategory.php
resources/views/issue-categories/index.blade.php
app/Enums/KategoriIsuEnum.php
app/Enums/KategoriPermintaanEnum.php
app/Enums/JenisBackupEnum.php   (kalau pernah disalin dari zip versi lama)
```
