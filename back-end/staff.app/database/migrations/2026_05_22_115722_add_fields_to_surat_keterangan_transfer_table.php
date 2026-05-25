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
        Schema::table('surat_keterangan_transfer', function (Blueprint $table) {
            $table->string('tempat_lahir')->nullable()->after('tanggal_lahir');
            $table->text('alamat')->nullable()->after('tahun_akademik');
            $table->string('universitas_tujuan')->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keterangan_transfer', function (Blueprint $table) {
            $table->dropColumn(['tempat_lahir', 'alamat', 'universitas_tujuan']);
        });
    }
};
