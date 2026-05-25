<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$jenisSurat = \App\Models\JenisSurat::all(['id', 'alias', 'format_surat']);
echo json_encode($jenisSurat, JSON_PRETTY_PRINT);
