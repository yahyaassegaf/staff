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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 255);
            $table->string('nim', 255);
            $table->bigInteger('nik');
            $table->date('tgl_lahir');
            $table->string('nilai_akreditasi', 255);
            $table->string('nomor_sk_ban_pt', 255);
            $table->string('nomor_ijazah_nasional', 255);
            $table->string('tanggal_sk_yudisium', 255);
            $table->string('tanggal_penerbitan', 255);
            $table->unsignedBigInteger('prodi_id');
            $table->foreign('prodi_id')->references('id')->on('prodi');
            $table->enum('status', ['belum', 'sudah'])->default('belum');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
