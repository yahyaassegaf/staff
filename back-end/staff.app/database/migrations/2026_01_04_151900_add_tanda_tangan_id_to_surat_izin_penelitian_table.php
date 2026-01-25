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
            if (!Schema::hasColumn('surat_izin_penelitian', 'tanda_tangan_id')) {
                $table->integer('tanda_tangan_id')->nullable()->after('prodi_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_izin_penelitian', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_id');
        });
    }
};
