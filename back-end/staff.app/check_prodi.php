<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$prodi = \App\Models\Prodi::find(10);
if ($prodi) {
    echo "PRODI 10 EXISTS: " . $prodi->nama . "\n";
} else {
    echo "PRODI 10 DOES NOT EXIST!\n";
}
