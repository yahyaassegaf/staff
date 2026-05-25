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
        Schema::table('surat_keterangan_6', function (Blueprint $table) {
            $table->string('drive_file_id', 255)->nullable()->after('prodi_id');
            $table->string('local_path', 255)->nullable()->after('drive_file_id');
            $table->string('drive_link', 255)->nullable()->after('local_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keterangan_6', function (Blueprint $table) {
            $table->dropColumn(['drive_file_id', 'local_path', 'drive_link']);
        });
    }
};
