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
        $tables = [
            'hasil_rapat',
            'surat_izin_penelitian',
            'surat_keterangan',
            'surat_keterangan_6',
            'surat_keterangan_administrasi_keuangan',
            'surat_keterangan_aktif_mahasiswa',
            'surat_keterangan_daftar_s2',
            'surat_keterangan_kkn',
            'surat_keterangan_lulus_mata_kuliah',
            'surat_keterangan_ppl',
            'surat_keterangan_qismul_aman',
            'surat_keterangan_spm',
            'surat_keterangan_tasma_kkn_ppl',
            'surat_keterangan_transfer',
            'surat_keterangan_ujian_komprehensif_diniyah',
            'surat_pernyataan_verifikasi_nilai',
            'surat_tugas',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->enum('petanda_tangan', ['ya', 'tidak'])->default('tidak');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'hasil_rapat',
            'surat_izin_penelitian',
            'surat_keterangan',
            'surat_keterangan_6',
            'surat_keterangan_administrasi_keuangan',
            'surat_keterangan_aktif_mahasiswa',
            'surat_keterangan_daftar_s2',
            'surat_keterangan_kkn',
            'surat_keterangan_lulus_mata_kuliah',
            'surat_keterangan_ppl',
            'surat_keterangan_qismul_aman',
            'surat_keterangan_spm',
            'surat_keterangan_tasma_kkn_ppl',
            'surat_keterangan_transfer',
            'surat_keterangan_ujian_komprehensif_diniyah',
            'surat_pernyataan_verifikasi_nilai',
            'surat_tugas',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'petanda_tangan')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('petanda_tangan');
                });
            }
        }
    }
};
