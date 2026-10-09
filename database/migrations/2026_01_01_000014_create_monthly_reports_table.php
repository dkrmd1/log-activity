<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('status');                       // submitted | approved | rejected
            $table->string('data_fingerprint', 64);         // "sidik jari" data bulan itu saat diajukan

            // Dibuat oleh (Staf) - disalin saat diajukan, supaya laporan lama tidak berubah kalau profil berubah
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('prepared_name');
            $table->string('prepared_position');
            $table->string('prepared_signature')->nullable();
            $table->timestamp('prepared_at');

            // Diketahui oleh (Group Head) - diisi saat ACC / ditolak
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reviewed_name')->nullable();
            $table->string('reviewed_position')->nullable();
            $table->string('reviewed_signature')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();        // alasan kalau ditolak

            $table->timestamps();

            $table->unique(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
