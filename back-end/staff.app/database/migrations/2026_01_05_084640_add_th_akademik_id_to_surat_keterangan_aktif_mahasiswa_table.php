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
        Schema::table('surat_keterangan_aktif_mahasiswa', function (Blueprint $table) {
            $table->unsignedBigInteger('th_akademik_id')->nullable()->after('prodi_id');
            $table->foreign('th_akademik_id')->references('id')->on('th_akademik')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keterangan_aktif_mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['th_akademik_id']);
            $table->dropColumn('th_akademik_id');
        });
    }
};
