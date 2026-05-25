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
        Schema::create('setting_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('kunci_jabatan')->unique(); // Contoh isi: 'kepala_biro_keuangan'
            $table->string('nama_jabatan'); // Contoh isi: 'Kepala Biro Administrasi Keuangan'
            $table->foreignId('tanda_tangan_id')->constrained('tanda_tangan'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_jabatan');
    }
};
