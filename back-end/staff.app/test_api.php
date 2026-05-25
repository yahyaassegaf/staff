<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/api/skk/2', // Use a valid ID from the database
        'PUT',
        [
            'prodi_id' => 1,
            'no_surat' => '888',
            'ketua' => 'Ketua Test',
            'nama_mhs' => 'Test Mahasiswa',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '123456',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'A',
            'tanggal' => '2025-05-25'
        ],
        [],
        [],
        ['HTTP_AUTHORIZATION' => 'Bearer token']
    )
);

echo $response->getContent();
