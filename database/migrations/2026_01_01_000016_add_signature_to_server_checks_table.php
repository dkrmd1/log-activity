<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_checks', function (Blueprint $table) {
            // Tanda tangan penanggung jawab form (blok "Tanggal / tanda tangan / nama" di kanan bawah form)
            $table->foreignId('signed_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('signed_name')->nullable()->after('signed_by');       // nama disalin saat tanda tangan
            $table->string('signed_signature')->nullable()->after('signed_name'); // salinan file tanda tangan (disk privat)
            $table->timestamp('signed_at')->nullable()->after('signed_signature');
        });
    }

    public function down(): void
    {
        Schema::table('server_checks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by');
            $table->dropColumn(['signed_name', 'signed_signature', 'signed_at']);
        });
    }
};
