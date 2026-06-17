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
        Schema::table('template_ijazah', function (Blueprint $table) {
            $table->json('teks_statis')->nullable()->after('orientasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_ijazah', function (Blueprint $table) {
            $table->dropColumn('teks_statis');
        });
    }
};
