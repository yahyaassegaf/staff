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
        Schema::create('surat_tugas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama_dosen');
            $table->string('alamat_dosen');
            $table->string('tugas_dosen');
            $table->text('tugasnya');
            $table->string('nama_mhs');
            $table->string('nim_nik');
            $table->string('fakultas_prodi');
            $table->text('judul_skripsi');
            $table->string('masa_penugasan');
            $table->date('tanggal');
            $table->integer('user_id');
            $table->integer('prodi_id');
             $table->enum('jenis_kelamin',['L','P'])->nullable();
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
        Schema::dropIfExists('surat_tugas');
    }
};
