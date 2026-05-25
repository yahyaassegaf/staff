<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    $c = app(App\Http\Controllers\Api\Sk6Controller::class);
    $latest = \App\Models\SuratKeterangan6::orderBy('id', 'desc')->first();
    if (!$latest) {
        echo "No data found\n";
        exit;
    }
    echo "Generating PDF for ID: " . $latest->id . "\n";
    $c->downloadPdf($latest->id);
    echo "SUCCESS\n";
} catch (\Throwable $e) {
    echo "ERROR: " . (string)$e . "\n";
}
