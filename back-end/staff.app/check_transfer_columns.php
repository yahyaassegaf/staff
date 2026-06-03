<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $cols = DB::select("describe surat_keterangan_transfer");
    echo "TABLE: surat_keterangan_transfer\n";
    foreach ($cols as $col) {
        echo " - {$col->Field} ({$col->Type}) Null: {$col->Null}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
