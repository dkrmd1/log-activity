<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->date('occurred_at');                 // Tanggal Terjadi
            $table->string('category');                  // Kategori Isu
            $table->string('title');                      // Isu
            $table->text('description')->nullable();      // Deskripsi
            $table->string('requester');                  // Pemohon
            $table->text('follow_up')->nullable();         // Tindak Lanjut Isu
            $table->date('completed_at')->nullable();     // Tanggal Selesai
            $table->string('status')->default('Open');    // Status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
