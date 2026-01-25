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
        Schema::create('th_akademik', function (Blueprint $table) {
            $table->id();

            $table->char('kode', 5)->nullable();
            $table->string('nama', 50)->nullable();

            $table->enum('semester', ['Ganjil', 'Genap', 'Pendek'])->nullable();
            $table->enum('aktif', ['Y', 'T'])->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->integer('id_awal')->nullable();
            $table->string('token', 50)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('th_akademik');
    }
};
