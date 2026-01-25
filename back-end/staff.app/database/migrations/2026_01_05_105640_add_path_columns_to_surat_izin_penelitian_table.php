<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_izin_penelitian', function (Blueprint $table) {
            $table->string('local_path')->nullable()->after('status');
            $table->string('drive_link')->nullable()->after('local_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_izin_penelitian', function (Blueprint $table) {
            $table->dropColumn(['local_path', 'drive_link']);
        });
    }
};
