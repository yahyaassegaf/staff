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
        Schema::create('posisi_template', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_id');
            $table->foreign('template_id')->references('id')->on('template_ijazah')->onDelete('cascade');

            $table->string('field_name', 50);
            $table->string('label_display', 50)->nullable();
            $table->integer('posisi_x')->default(0);
            $table->integer('posisi_y')->default(0);
            $table->integer('font_size')->default(12);
            $table->string('font_family', 50)->default('Arial');
            $table->enum('font_weight', ['normal', 'bold'])->default('normal');
            $table->string('text_color', 7)->default('#000000');
            $table->enum('alignment', ['left', 'center', 'right'])->default('left');
            $table->integer('urutan')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posisi_template');
    }
};
