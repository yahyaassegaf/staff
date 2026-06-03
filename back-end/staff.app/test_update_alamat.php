<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request();
$request->setMethod('PUT');
$request->merge([
    'prodi_id' => 10,
    'nama_mhs' => 'Testing MHS',
    'tempat_lahir' => 'Jakarta',
    'tanggal_lahir' => '2000-01-01',
    'nim' => '111111111',
    'prodi_mhs' => 'Teknik Informatika',
    'alamat_rumah' => 'ALAMAT BARU DARI TEST SCRIPT',
    'kelas_pondok' => 'Kelas A',
    'tanggal' => '2026-05-27',
    'no_sklmk' => '123',
    'no_skak' => '124',
    'no_sktkp' => '125',
    'no_skqa' => '126',
    'no_skukd' => '127',
    'tanggal_berlaku_dari' => '2026-05-27',
    'tanggal_berlaku_sampai' => '2026-05-27',
]);

Auth::loginUsingId(1);
$sk6 = App\Models\SuratKeterangan6::first();
if ($sk6) {
    echo "SK6 ID: " . $sk6->id . "\n";
    $controller = new App\Http\Controllers\Api\Sk6Controller();
    $response = $controller->update($request, $sk6->id);
    echo "Update response: " . $response->getContent() . "\n";

    $sklmk = App\Models\SuratKeteranganLulusMataKuliah::where('nim', '111111111')->first();
    echo "Alamat di SKLMK setelah update: " . $sklmk->alamat_rumah . "\n";
} else {
    echo "No SK6 found.\n";
}

$sktkp = App\Models\SuratKeteranganTasmaKknPpl::first();
if ($sktkp) {
    echo "SKTKP ID: " . $sktkp->id . "\n";
    $requestTasma = new \Illuminate\Http\Request();
    $requestTasma->setMethod('PUT');
    $requestTasma->merge([
        'no_surat' => '125',
        'prodi_id' => 10,
        'nama_mhs' => 'Testing MHS',
        'tempat_lahir' => 'Jakarta',
        'tanggal_lahir' => '2000-01-01',
        'nim' => '111111111',
        'prodi_mhs' => 'Teknik Informatika',
        'alamat_rumah' => 'ALAMAT BARU DARI TEST SCRIPT TASMA',
        'kelas_pondok' => 'Kelas A',
        'tanggal' => '2026-05-27',
    ]);
    
    $controllerTasma = new App\Http\Controllers\Api\SuratKeteranganTasmaKknPplController();
    $response = $controllerTasma->update($requestTasma, $sktkp->id);
    echo "Update TASMA response: " . $response->getContent() . "\n";
    
    $sktkp_after = App\Models\SuratKeteranganTasmaKknPpl::find($sktkp->id);
    echo "Alamat di TASMA setelah update: " . $sktkp_after->alamat_rumah . "\n";
} else {
    echo "No SKTKP found.\n";
}

