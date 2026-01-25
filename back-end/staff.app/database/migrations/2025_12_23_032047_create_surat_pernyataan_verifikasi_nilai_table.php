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
        Schema::create('surat_pernyataan_verifikasi_nilai', function (Blueprint $table) {
            $table->id();
            $table->string('nomor');
            $table->string('niy');
            $table->string('jabatan');
            $table->string('nama_mahasiswa');
            $table->string('nim');
            $table->string('prodi_mhs')->nullable();
            $table->string('fakultas');
            $table->date('tanggal');
            $table->integer('prodi_id');
            $table->integer('user_id');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->integer('tanda_tangan_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pernyataan_verifikasi_nilai');
    }
};
