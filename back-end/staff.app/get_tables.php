<?php
require __DIR__.'/vendor/autoload.php';
\ = require_once __DIR__.'/bootstrap/app.php';
\->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\ = [
    'App\Models\HasilRapat',
    'App\Models\SuratIzinPenelitian',
    'App\Models\SuratKeterangan',
    'App\Models\SuratKeterangan6',
    'App\Models\SuratKeteranganAdministrasiKeuangan',
    'App\Models\SuratKeteranganAktifMahasiswa',
    'App\Models\SuratKeteranganDaftarS2',
    'App\Models\SuratKeteranganKkn',
    'App\Models\SuratKeteranganLulusMataKuliah',
    'App\Models\SuratKeteranganPpl',
    'App\Models\SuratKeteranganQismulAman',
    'App\Models\SuratKeteranganSpm',
    'App\Models\SuratKeteranganTasmaKknPpl',
    'App\Models\SuratKeteranganTransfer',
    'App\Models\SuratKeteranganUjianKomprehensifDiniyah',
    'App\Models\SuratPernyataanVerifikasiNilai',
    'App\Models\SuratTugas'
];

foreach (\ as \) {
    if (class_exists(\)) {
        \ = new \;
        echo \ . ' => ' . \->getTable() . PHP_EOL;
    } else {
        echo \ . ' => NOT FOUND' . PHP_EOL;
    }
}
