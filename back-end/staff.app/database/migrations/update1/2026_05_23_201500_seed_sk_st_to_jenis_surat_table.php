<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan tidak menduplikasi data
        if (DB::table('jenis_surat')->where('alias', 'SK')->count() == 0) {
            DB::table('jenis_surat')->insert([
                'alias' => 'SK',
                'nama' => 'SURAT KETERANGAN',
                'format_surat' => 'SU-{NO}/UII.085/K{PRODI}/PP.00/{BULAN}/{TAHUN}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('jenis_surat')->where('alias', 'ST')->count() == 0) {
            DB::table('jenis_surat')->insert([
                'alias' => 'ST',
                'nama' => 'SURAT TUGAS',
                'format_surat' => 'SU-{NO}/UII.085/K{PRODI}/PP.00/{BULAN}/{TAHUN}',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('jenis_surat')->whereIn('alias', ['SK', 'ST'])->delete();
    }
};
