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
        Schema::create('surat_keterangan_lulus_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat', 50)->unique();
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->string('nama_lengkap', 255);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('nim', 255);
            $table->string('prodi_mahasiswa', 255);
            $table->text('alamat_rumah');
            $table->string('kelas_pondok', 255);
            $table->date('tanggal');
            $table->integer('user_id');
            $table->enum('jenis_kelamin',['L','P'])->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->string('drive_file_id')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan_lulus_mata_kuliah');
    }
};
