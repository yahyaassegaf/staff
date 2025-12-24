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
        Schema::create('surat_keterangan_aktif_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->integer('prodi_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nim')->nullable();
            $table->string('nik')->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('prodi_mhs');
            $table->string('semester');
            $table->string('tahun_akademik');
            $table->string('nama_ortu');
            $table->string('nik_ortu')->nullable();
            $table->string('nip_ortu')->nullable();
            $table->text('alamat_ortu');
            $table->string('hp_ortu')->nullable();
            $table->date('tanggal');
            $table->integer('user_id');
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan_aktif_mahasiswa');
    }
};
