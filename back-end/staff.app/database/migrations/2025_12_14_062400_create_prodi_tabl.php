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
        Schema::create('prodi', function (Blueprint $table) {
            $table->id();
            $table->string('kprodi', 50)->nullable()->comment('Kode Prodi tambahan');
            $table->string('kode', 50)->nullable();
            $table->string('konim', 3)->nullable();
            $table->string('alias', 5)->nullable();
            $table->string('nama', 75)->nullable();
            $table->enum('aktif', ['Y', 'T'])->default('T');
            $table->char('jenjang', 5)->nullable();
            $table->string('nidn_kepala', 15)->nullable();
            $table->string('nama_kepala', 60)->nullable();
            $table->char('akreditasi', 5)->nullable();
            $table->string('color', 15)->nullable();
            $table->integer('max_sks_skripsi')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('tanda_tangan_id')->nullable();
            $table->timestamps();
            $table->string('token', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
