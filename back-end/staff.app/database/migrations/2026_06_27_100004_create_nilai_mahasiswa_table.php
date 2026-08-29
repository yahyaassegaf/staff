<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nilai_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tanggal_nilai_id')->nullable();
            $table->unsignedBigInteger('mahasiswa_id')->nullable();
            $table->string('nim', 20)->nullable();
            $table->string('nama_mhs', 50)->nullable();
            $table->string('kode_mk', 20)->nullable();
            $table->string('nama_mk', 255)->nullable();
            $table->integer('sks_mk')->nullable();
            $table->integer('smt_mk')->nullable();
            $table->double('nilai_akhir', 5, 2)->nullable();
            $table->double('nilai_bobot', 5, 2)->nullable();
            $table->string('nilai_huruf', 5)->nullable();
            $table->enum('transkrip', ['Y', 'T'])->nullable();
            $table->timestamps();

            $table->foreign('tanggal_nilai_id')->references('id')->on('tanggal_nilai')->onDelete('cascade');
            $table->foreign('mahasiswa_id')->references('id')->on('mahasiswa')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nilai_mahasiswa');
    }
};
