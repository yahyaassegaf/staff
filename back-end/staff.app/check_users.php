<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = \App\Models\User::all(['id', 'name', 'username', 'level_id', 'prodi_id', 'jenis_kelamin']);
echo json_encode($users, JSON_PRETTY_PRINT);
