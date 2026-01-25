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
        Schema::create('surat_izin_penelitian', function (Blueprint $table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama');
            $table->string('nim');
            $table->string('prodi_mhs')->nullable();
            $table->string('semester');
            $table->date('dari_tanggal');
            $table->date('tanggal');
            $table->integer('prodi_id');
            $table->integer('user_id');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
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
        Schema::dropIfExists('surat_izin_penelitian');
    }
};
