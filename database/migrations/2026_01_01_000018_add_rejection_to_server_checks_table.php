<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_checks', function (Blueprint $table) {
            // Penolakan form oleh Group Head (wajib disertai keterangan)
            $table->foreignId('rejected_by')->nullable()->after('signed_at')->constrained('users')->nullOnDelete();
            $table->string('rejected_name')->nullable()->after('rejected_by');   // nama disalin saat menolak
            $table->text('reject_note')->nullable()->after('rejected_name');     // keterangan penolakan
            $table->timestamp('rejected_at')->nullable()->after('reject_note');
        });
    }

    public function down(): void
    {
        Schema::table('server_checks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn(['rejected_name', 'reject_note', 'rejected_at']);
        });
    }
};
