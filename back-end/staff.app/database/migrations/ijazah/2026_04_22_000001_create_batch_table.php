<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch', function (Blueprint $table) {
            $table->id();
            $table->string('nama_batch', 255);
            $table->date('tanggal_import');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch');
    }
};