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
        Schema::create('hasil_rapat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->nullable();
            $table->foreignId('prodi_id')->nullable()->constrained('prodi')->onDelete('set null');
            $table->string('agenda');
            $table->date('tanggal');
            $table->time('waktu')->nullable();
            $table->string('tempat')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->text('pembahasan')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->string('drive_file_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_rapat');
    }
};
