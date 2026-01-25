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
        Schema::table('surat_keterangan_ujian_komprehensif_diniyah', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_keterangan_ujian_komprehensif_diniyah', 'tanda_tangan_id')) {
                $table->integer('tanda_tangan_id')->nullable()->after('koor_komprehensif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keterangan_ujian_komprehensif_diniyah', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_id');
        });
    }
};
