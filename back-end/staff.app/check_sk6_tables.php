<?php

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = [
    'surat_keterangan_lulus_mata_kuliah',
    'surat_keterangan_administrasi_keuangan',
    'surat_keterangan_tasma_kkn_ppl',
    'surat_keterangan_qismul_aman',
    'surat_keterangan_ujian_komprehensif_diniyah'
];

foreach ($tables as $table) {
    try {
        $cols = DB::select("describe $table");
        echo "TABLE: $table\n";
        foreach ($cols as $col) {
            echo " - {$col->Field} ({$col->Type}) Null: {$col->Null}\n";
        }
    } catch (\Exception $e) {
        echo "Error on $table: " . $e->getMessage() . "\n";
    }
}
