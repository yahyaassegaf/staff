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
        Schema::create('surat_keterangan_spm', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->integer('prodi_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nim')->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('prodi_mhs');
            $table->text('alamat');
            $table->string('nama_ortu');
            $table->string('tempat_tugas');
            $table->text('alamat_tugas');
            $table->string('tahun');
            $table->string('semester');
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
        Schema::dropIfExists('surat_keterangan_spm');
    }
};
