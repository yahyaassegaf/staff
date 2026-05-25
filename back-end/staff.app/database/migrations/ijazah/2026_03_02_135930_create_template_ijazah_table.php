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
        Schema::create('template_ijazah', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prodi_id')->nullable()->comment('null = global');
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3'])->nullable()->comment('null = semua jenjang');
            $table->string('nama_template', 100);
            $table->string('file_background', 255)->nullable();
            $table->enum('ukuran_kertas', ['A4', 'A3', 'Legal', 'F4'])->default('A4');
            $table->enum('orientasi', ['portrait', 'landscape'])->default('portrait');
            $table->enum('is_active', ['aktif', 'tidak'])->default('aktif');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_ijazah');
    }
};
