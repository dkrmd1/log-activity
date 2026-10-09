<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->date('occurred_at');                 // Tanggal Terjadi
            $table->string('category');                  // Kategori Permintaan
            $table->string('title');                      // Permintaan
            $table->string('requester');                  // Pemohon
            $table->text('follow_up')->nullable();         // Status Permintaan (keterangan tindak lanjut)
            $table->date('completed_at')->nullable();     // Tanggal Selesai
            $table->string('status')->default('Open');    // Status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
