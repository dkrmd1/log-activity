<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signature')->nullable()->after('avatar'); // path file tanda tangan (disk privat)
        });

        // Role: 'admin', 'group_head', 'staff' (kolom role sudah string biasa, tidak perlu diubah).
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature');
        });
    }
};
