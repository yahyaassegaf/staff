<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$data = \App\Models\SuratKeteranganQismulAman::first(); 
if ($data) {
    $nomorStr = $data->nomor_surat ?? $data->nomor ?? null;
    if ($nomorStr) {
        $parts = explode('/', $nomorStr);
        $firstPart = $parts[0];
        if (strpos($firstPart, '-') !== false) {
            $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
        }
        $data->no_surat = trim($firstPart);
    }
    echo json_encode($data);
} else {
    echo 'No data';
}
