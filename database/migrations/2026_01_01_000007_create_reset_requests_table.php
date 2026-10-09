<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reset_requests', function (Blueprint $table) {
            $table->id();
            $table->date('requested_at');           // Tanggal permohonan
            $table->string('account_user_id');       // User ID nasabah/akun terkait
            $table->string('name');                  // Nama
            $table->string('email');                 // Email
            $table->json('types');                   // Jenis permohonan, bisa lebih dari satu: ["Reset PIN","Unblock"]
            $table->text('notes')->nullable();        // Keterangan tambahan (opsional)
            $table->string('status')->default('Open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reset_requests');
    }
};
