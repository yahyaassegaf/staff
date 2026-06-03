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
    $count = DB::table($table)->where('nim', '111111111')->count();
    echo "$table: $count\n";
}
