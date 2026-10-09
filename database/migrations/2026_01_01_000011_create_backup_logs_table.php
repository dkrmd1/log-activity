<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('jenis');                  // Database Cpanel, Konfigurasi Mikrotik, dst
            $table->date('backup_date');              // Tanggal backup dilakukan
            $table->time('backup_time');              // Jam backup dilakukan
            $table->text('notes')->nullable();        // Keterangan tambahan (opsional)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Dicatat oleh (otomatis dari user login)
            $table->timestamps();

            $table->index('backup_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
