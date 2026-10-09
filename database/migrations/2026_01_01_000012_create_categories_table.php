<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu tabel untuk semua pilihan dropdown yang bisa dikelola admin:
     *   type = isu        -> Kategori Isu
     *   type = permintaan -> Kategori Permintaan
     *   type = backup     -> Jenis Backup
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->timestamps();

            $table->unique(['type', 'name']);
        });

        // ---- Isi awal: bawa data lama supaya dropdown yang sudah ada tidak berubah ----

        // Kategori Isu: dari tabel issue_categories lama + kategori yang sudah terpakai di data isu.
        $isu = collect();
        if (Schema::hasTable('issue_categories')) {
            $isu = $isu->merge(DB::table('issue_categories')->pluck('name'));
        }
        if (Schema::hasTable('issues')) {
            $isu = $isu->merge(DB::table('issues')->distinct()->pluck('category'));
        }
        if ($isu->filter()->isEmpty()) {
            $isu = collect(['Sistem/Aplikasi', 'Data/SDI', 'Approval/Reject', 'Jaringan/Infra', 'Lainnya']);
        }

        // Kategori Permintaan: daftar bawaan + yang sudah terpakai.
        $permintaan = collect(['Penambahan Data', 'Perubahan Konfigurasi', 'Akses/Kode Sales', 'Perbaikan Fitur', 'Lainnya']);
        if (Schema::hasTable('requests')) {
            $permintaan = $permintaan->merge(DB::table('requests')->distinct()->pluck('category'));
        }

        // Jenis Backup: daftar bawaan + yang sudah terpakai.
        $backup = collect(['Database Cpanel', 'Konfigurasi Mikrotik', 'Database Aplikasi Intranet', 'Database NAS', 'Database BOFIS']);
        if (Schema::hasTable('backup_logs')) {
            $backup = $backup->merge(DB::table('backup_logs')->distinct()->pluck('jenis'));
        }

        $now = now();

        foreach (['isu' => $isu, 'permintaan' => $permintaan, 'backup' => $backup] as $type => $names) {
            foreach ($names->filter()->unique()->values() as $name) {
                DB::table('categories')->insertOrIgnore([
                    'type' => $type,
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
