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
        Schema::create('surat_keterangan_daftar_s2', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->integer('prodi_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nim')->nullable();
            $table->string('prodi');
            $table->text('keterangan');
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->date('tanggal');
            $table->integer('user_id');
            $table->enum('jenis_kelamin',['L','P'])->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keterangan_daftar_s2');
    }
};
