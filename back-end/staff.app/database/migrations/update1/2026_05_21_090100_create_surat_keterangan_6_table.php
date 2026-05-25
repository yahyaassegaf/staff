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
        Schema::create('surat_keterangan_6', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mhs');
            $table->string('nim');
            $table->date('tanggal');
            $table->foreignId('prodi_id')->constrained('prodi');
            
            $table->unsignedBigInteger('surat_keterangan_lulus_mata_kuliah_id');
            $table->foreign('surat_keterangan_lulus_mata_kuliah_id', 'fk_sk6_sklmk')->references('id')->on('surat_keterangan_lulus_mata_kuliah');
            
            $table->unsignedBigInteger('surat_keterangan_administrasi_keuangan_id');
            $table->foreign('surat_keterangan_administrasi_keuangan_id', 'fk_sk6_skak')->references('id')->on('surat_keterangan_administrasi_keuangan');
            
            $table->unsignedBigInteger('surat_keterangan_tasma_kkn_ppl_id');
            $table->foreign('surat_keterangan_tasma_kkn_ppl_id', 'fk_sk6_sktkp')->references('id')->on('surat_keterangan_tasma_kkn_ppl');
            
            $table->unsignedBigInteger('surat_keterangan_ujian_komprehensif_diniyah_id');
            $table->foreign('surat_keterangan_ujian_komprehensif_diniyah_id', 'fk_sk6_skukd')->references('id')->on('surat_keterangan_ujian_komprehensif_diniyah');
            
            $table->unsignedBigInteger('surat_keterangan_qismul_aman_id');
            $table->foreign('surat_keterangan_qismul_aman_id', 'fk_sk6_skqa')->references('id')->on('surat_keterangan_qismul_aman');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan_6');
    }
};
