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
        Schema::create('surat_keterangan_ujian_komprehensif_diniyah', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->integer('prodi_id');
            $table->string('koor_komprehensif')->nullable();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nim');
            $table->string('prodi_mhs');
            $table->text('alamat_rumah');
            $table->string('kelas_pondok');
            $table->date('tanggal');
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->string('ttd')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan_ujian_komprehensif_diniyah');
    }
};
