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
        Schema::table('surat_keterangan_administrasi_keuangan', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_keterangan_administrasi_keuangan', 'tanda_tangan_id')) {
                $table->integer('tanda_tangan_id')->nullable()->after('kepala_biro');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keterangan_administrasi_keuangan', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_id');
        });
    }
};
