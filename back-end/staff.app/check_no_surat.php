<?php
$controllers = [
    'Sk6Controller.php',
    'SuratIzinPenelitianController.php',
    'SuratKeteranganAdministrasiKeuanganController.php',
    'SuratKeteranganAktifMahasiswaController.php',
    'SuratKeteranganController.php',
    'SuratKeteranganDaftarS2Controller.php',
    'SuratKeteranganKknController.php',
    'SuratKeteranganLulusMataKuliahController.php',
    'SuratKeteranganPplController.php',
    'SuratKeteranganQismulAmanController.php',
    'SuratKeteranganSpmController.php',
    'SuratKeteranganTasmaKknPplController.php',
    'SuratKeteranganTransferController.php',
    'SuratKeteranganUjianKomprehensifDiniyahController.php',
    'SuratPernyataanVerifikasiNilaiController.php',
    'SuratTugasController.php'
];

foreach ($controllers as $c) {
    $path = 'app/Http/Controllers/Api/' . $c;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if (strpos($content, 'orderByDesc(\'id\')->value(\'nomor\')') !== false || strpos($content, 'orderByDesc("id")->value("nomor")') !== false || strpos($content, 'orderByDesc(\'id\')->first()') !== false) {
            echo $c . ' -> AUTO GENERATE' . PHP_EOL;
        } else if (strpos($content, '\'no_surat\' => \'required') !== false || strpos($content, 'no_sklmk') !== false || strpos($content, '"no_surat" => "required') !== false) {
            echo $c . ' -> MANUAL INPUT' . PHP_EOL;
        } else {
            echo $c . ' -> UNKNOWN' . PHP_EOL;
        }
    } else {
        echo $c . ' -> NOT FOUND' . PHP_EOL;
    }
}
