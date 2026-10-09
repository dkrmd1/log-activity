<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Master: bagian (A, B, C, ...) dan item pemeriksaan, dikelola admin ----
        Schema::create('control_sections', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5);                       // A, B, C, ...
            $table->string('title');                         // Pemeriksaan Switch HUB, Router & Internet
            $table->boolean('merge_notes')->default(false);  // true = kolom Keterangan digabung 1 sel untuk seluruh bagian
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('control_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('control_sections')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ---- Form harian: 1 baris per tanggal ----
        Schema::create('server_checks', function (Blueprint $table) {
            $table->id();
            $table->date('check_date')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Isi form harian. Nama bagian & item DISALIN (snapshot) saat form dibuat, jadi
        // mengubah/menghapus master item tidak mengubah form hari-hari sebelumnya.
        Schema::create('server_check_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_check_id')->constrained()->cascadeOnDelete();

            $table->string('section_code', 5);
            $table->string('section_title');
            $table->boolean('merge_notes')->default(false);
            $table->unsignedSmallInteger('item_no');
            $table->string('item_name');
            $table->unsignedSmallInteger('sort_order')->default(0);

            // 3 waktu pemeriksaan: 08:00, 12:00, 16:30  (kondisi: OK / NOK, keterangan bebas)
            $table->string('c1', 10)->nullable();
            $table->text('n1')->nullable();
            $table->string('c2', 10)->nullable();
            $table->text('n2')->nullable();
            $table->string('c3', 10)->nullable();
            $table->text('n3')->nullable();

            $table->foreignId('paraf_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['server_check_id', 'sort_order']);
        });

        // ---- Isi awal sesuai form yang ada ----
        $now = now();
        $seed = [
            ['A', 'Pemeriksaan Switch HUB, Router & Internet', true, [
                'Switch menyala dengan normal',
                'Router menyala dengan normal',
                'Mikrotik menyala dengan normal',
                'Firewall menyala dengan normal',
                'Server Jabartel menyala dengan normal',
                'Distribution Panel UPS menyala dengan normal',
                'Modem Data Utama menyala dengan normal',
                'Modem Link Net menyala dengan normal',
                'Wifi menyala dengan normal',
                'Modem sumber internet menyala dengan normal',
                'Panel Kontrol AC - R.SERVER menyala dengan normal',
                'Modem Telkom menyala dengan normal',
                'Panel Distribusi AC Menyala dengan normal',
                'SUB Distribution Panel Penarikan daya menyala dengan normal',
                'Panel Box Telepon menyala dengan normal',
                'Panasonic TES824 menyala dengan normal',
                'Mikrotik dapat di akses melalui aplikasi',
                'CCTV menyala dengan normal',
            ]],
            ['B', 'Pemeriksaan UPS & Stabilizer Server', false, [
                'lampu indikator UPS normal',
            ]],
            ['C', 'Pemeriksaan Kelistrikan & AC', false, [
                'Cek Suhu AC',
                'AC dapat bekerja dengan baik',
            ]],
        ];

        foreach ($seed as $i => [$code, $title, $merge, $items]) {
            $sectionId = DB::table('control_sections')->insertGetId([
                'code' => $code, 'title' => $title, 'merge_notes' => $merge,
                'sort_order' => ($i + 1) * 10, 'created_at' => $now, 'updated_at' => $now,
            ]);

            foreach ($items as $j => $name) {
                DB::table('control_items')->insert([
                    'section_id' => $sectionId, 'name' => $name, 'sort_order' => ($j + 1) * 10,
                    'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('server_check_entries');
        Schema::dropIfExists('server_checks');
        Schema::dropIfExists('control_items');
        Schema::dropIfExists('control_sections');
    }
};
