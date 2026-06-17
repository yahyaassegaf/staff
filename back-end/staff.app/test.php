<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$prodis = \App\Models\Prodi::pluck('alias')->toArray();
$jabatan = \App\Models\SettingJabatan::pluck('kunci_jabatan')->toArray();

echo "Prodi Aliases:\n";
print_r($prodis);
echo "SettingJabatan Keys:\n";
print_r($jabatan);
