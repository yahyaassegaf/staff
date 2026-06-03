<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$records = \App\Models\SuratKeteranganTransfer::all();
echo "TOTAL RECORDS: " . $records->count() . "\n";
echo json_encode($records, JSON_PRETTY_PRINT);
