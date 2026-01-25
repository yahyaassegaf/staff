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
            $table->string('kepada', 255)->nullable()->after('prodi_mhs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_izin_penelitian', function (Blueprint $table) {
            $table->dropColumn('kepada');
        });
    }
};
