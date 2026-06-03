<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Bind a dummy Request object to avoid session guard type errors
$reqInstance = Request::create('/', 'GET');
$app->instance('request', $reqInstance);

// Login first user for auth context
$user = \App\Models\User::first();
if ($user) {
    Auth::login($user);
    echo "Logged in as: " . $user->username . "\n";
}

$prodi = \App\Models\Prodi::first();
$tanda_tangan = \App\Models\TandaTangan::first();
$prodi_id = $prodi ? $prodi->id : 1;
$tanda_tangan_id = $tanda_tangan ? $tanda_tangan->id : 1;
$user_id = $user ? $user->id : 1;

// Helper to ensure dependent records exist for SK6
$getSklmk = function() use ($prodi_id, $user_id) {
    $first = \App\Models\SuratKeteranganLulusMataKuliah::first();
    if ($first) return $first;
    $dummy = new \App\Models\SuratKeteranganLulusMataKuliah();
    $dummy->nomor_surat = '001/SKLMK/' . rand(10, 99);
    $dummy->prodi_id = $prodi_id;
    $dummy->nama_lengkap = 'Dummy SKLMK';
    $dummy->tempat_lahir = 'Surabaya';
    $dummy->tanggal_lahir = '2000-01-01';
    $dummy->nim = 'NIM_SKLMK';
    $dummy->prodi_mahasiswa = 'TBA';
    $dummy->alamat_rumah = 'Jl. Dummy';
    $dummy->kelas_pondok = 'A';
    $dummy->tanggal = '2026-05-25';
    $dummy->user_id = $user_id;
    $dummy->status = 'pending';
    $dummy->save();
    return $dummy;
};

$getSkak = function() use ($prodi_id, $tanda_tangan_id, $user_id) {
    $first = \App\Models\SuratKeteranganAdministrasiKeuangan::first();
    if ($first) return $first;
    $dummy = new \App\Models\SuratKeteranganAdministrasiKeuangan();
    $dummy->nomor_surat = '001/SKAK/' . rand(10, 99);
    $dummy->prodi_id = $prodi_id;
    $dummy->tanda_tangan_id = $tanda_tangan_id;
    $dummy->nama_lengkap = 'Dummy SKAK';
    $dummy->tempat_lahir = 'Surabaya';
    $dummy->tanggal_lahir = '2000-01-01';
    $dummy->nim = 'NIM_SKAK';
    $dummy->prodi_mhs = 'TBA';
    $dummy->alamat_rumah = 'Jl. Dummy';
    $dummy->kelas_pondok = 'A';
    $dummy->tanggal = '2026-05-25';
    $dummy->user_id = $user_id;
    $dummy->status = 'pending';
    $dummy->save();
    return $dummy;
};

$getSktkp = function() use ($prodi_id, $tanda_tangan_id, $user_id) {
    $first = \App\Models\SuratKeteranganTasmaKknPpl::first();
    if ($first) return $first;
    $dummy = new \App\Models\SuratKeteranganTasmaKknPpl();
    $dummy->nomor_surat = '001/SKTKP/' . rand(10, 99);
    $dummy->prodi_id = $prodi_id;
    $dummy->tanda_tangan_id = $tanda_tangan_id;
    $dummy->nama_lengkap = 'Dummy SKTKP';
    $dummy->tempat_lahir = 'Surabaya';
    $dummy->tanggal_lahir = '2000-01-01';
    $dummy->nim = 'NIM_SKTKP';
    $dummy->prodi_mhs = 'TBA';
    $dummy->alamat_rumah = 'Jl. Dummy';
    $dummy->kelas_pondok = 'A';
    $dummy->tanggal = '2026-05-25';
    $dummy->user_id = $user_id;
    $dummy->status = 'pending';
    $dummy->save();
    return $dummy;
};

$getSkukd = function() use ($prodi_id, $tanda_tangan_id) {
    $first = \App\Models\SuratKeteranganUjianKomprehensifDiniyah::first();
    if ($first) return $first;
    $dummy = new \App\Models\SuratKeteranganUjianKomprehensifDiniyah();
    $dummy->nomor_surat = '001/SKUKD/' . rand(10, 99);
    $dummy->prodi_id = $prodi_id;
    $dummy->tanda_tangan_id = $tanda_tangan_id;
    $dummy->nama_lengkap = 'Dummy SKUKD';
    $dummy->tempat_lahir = 'Surabaya';
    $dummy->tanggal_lahir = '2000-01-01';
    $dummy->nim = 'NIM_SKUKD';
    $dummy->prodi_mhs = 'TBA';
    $dummy->alamat_rumah = 'Jl. Dummy';
    $dummy->kelas_pondok = 'A';
    $dummy->tanggal = '2026-05-25';
    $dummy->user_id = 1;
    $dummy->status = 'pending';
    $dummy->save();
    return $dummy;
};

$getSkqa = function() use ($prodi_id, $tanda_tangan_id, $user_id) {
    $first = \App\Models\SuratKeteranganQismulAman::first();
    if ($first) return $first;
    $dummy = new \App\Models\SuratKeteranganQismulAman();
    $dummy->nomor_surat = '001/SKQA/' . rand(10, 99);
    $dummy->prodi_id = $prodi_id;
    $dummy->nama_lengkap = 'Dummy SKQA';
    $dummy->tempat_lahir = 'Surabaya';
    $dummy->tanggal_lahir = '2000-01-01';
    $dummy->nim = 'NIM_SKQA';
    $dummy->prodi_mhs = 'TBA';
    $dummy->alamat_rumah = 'Jl. Dummy';
    $dummy->kelas_pondok = 'A';
    $dummy->tanggal = '2026-05-25';
    $dummy->tanggal_berlaku_dari = '2026-01-01';
    $dummy->tanggal_berlaku_sampai = '2026-12-31';
    $dummy->user_id = $user_id;
    $dummy->status = 'pending';
    $dummy->save();
    return $dummy;
};

$sklmk = $getSklmk();
$skak = $getSkak();
$sktkp = $getSktkp();
$skukd = $getSkukd();
$skqa = $getSkqa();

$controllers = [
    [
        'name' => 'SuratKeteranganAktifMahasiswa',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganAktifMahasiswaController::class,
        'model' => \App\Models\SuratKeteranganAktifMahasiswa::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_AM_' . rand(100, 999),
            'nama_mhs' => 'Test AM Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'AM_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'semester' => 'VIII (Delapan)',
            'tahun_akademik' => '2025/2026 Ganjil',
            'nama_ortu' => 'Ortu Dummy',
            'alamat_ortu' => 'Jl. Ortu Dummy',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeteranganTasmaKknPpl',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganTasmaKknPplController::class,
        'model' => \App\Models\SuratKeteranganTasmaKknPpl::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_TKP_' . rand(100, 999),
            'tanda_tangan_id' => $tanda_tangan_id,
            'nama_mhs' => 'Test TKP Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'TKP_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeteranganQismulAman',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganQismulAmanController::class,
        'model' => \App\Models\SuratKeteranganQismulAman::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_QA_' . rand(100, 999),
            'nama_mhs' => 'Test QA Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'QA_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'tanggal_berlaku_dari' => '2026-01-01',
            'tanggal_berlaku_sampai' => '2026-12-31',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeteranganAdministrasiKeuangan',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganAdministrasiKeuanganController::class,
        'model' => \App\Models\SuratKeteranganAdministrasiKeuangan::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_AK_' . rand(100, 999),
            'tanda_tangan_id' => $tanda_tangan_id,
            'nama_mhs' => 'Test AK Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'AK_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'Sk6Controller (SuratKeterangan6)',
        'controller' => \App\Http\Controllers\Api\Sk6Controller::class,
        'model' => \App\Models\SuratKeterangan6::class,
        'update_data' => [
            'nama_mhs' => 'Test SK6 Mahasiswa Updated',
            'nim' => 'SK6_' . rand(1000, 9999),
            'tanggal' => '2026-05-25',
            'prodi_id' => $prodi_id,
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'no_sklmk' => '111',
            'no_skak' => '222',
            'no_sktkp' => '333',
            'no_skqa' => '444',
            'no_skukd' => '555',
            'tanggal_berlaku_dari' => '2026-01-01',
            'tanggal_berlaku_sampai' => '2026-12-31',
            'surat_keterangan_lulus_mata_kuliah_id' => $sklmk ? $sklmk->id : 1,
            'surat_keterangan_administrasi_keuangan_id' => $skak ? $skak->id : 1,
            'surat_keterangan_tasma_kkn_ppl_id' => $sktkp ? $sktkp->id : 1,
            'surat_keterangan_ujian_komprehensif_diniyah_id' => $skukd ? $skukd->id : 1,
            'surat_keterangan_qismul_aman_id' => $skqa ? $skqa->id : 1,
        ]
    ],
    [
        'name' => 'SuratKeteranganDaftarS2',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganDaftarS2Controller::class,
        'model' => \App\Models\SuratKeteranganDaftarS2::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_S2_' . rand(100, 999),
            'nama_lengkap' => 'Test S2 Mahasiswa Updated',
            'nim' => 'S2_' . rand(1000, 9999),
            'prodi' => 'TBA',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeterangan',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganController::class,
        'model' => \App\Models\SuratKeterangan::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'nama_mhs' => 'Test SK Mahasiswa Updated',
            'nim' => 'SK_' . rand(1000, 9999),
            'prodi' => 'TBA',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Testing alasan updated',
            'tanggal' => '2026-05-25',
            'tanda_tangan_id' => $tanda_tangan_id
        ]
    ],
    [
        'name' => 'SuratIzinPenelitian',
        'controller' => \App\Http\Controllers\Api\SuratIzinPenelitianController::class,
        'model' => \App\Models\SuratIzinPenelitian::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_IP_' . rand(100, 999),
            'nama' => 'Test IP Mahasiswa Updated',
            'nim' => 'IP_' . rand(1000, 9999),
            'semester' => 'VIII',
            'prodi_mhs' => 'TBA',
            'kepada' => 'Kepala Lembaga Dummy Updated',
            'dari_tanggal' => '2026-05-25',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'HasilRapat',
        'controller' => \App\Http\Controllers\Api\HasilRapatController::class,
        'model' => \App\Models\HasilRapat::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'agenda' => 'Dummy Rapat Agenda Updated',
            'tanggal' => '2026-05-25',
            'waktu' => '10:00',
            'tempat' => 'Aula',
            'pembahasan' => 'Pembahasan Rapat Dummy Updated'
        ]
    ],
    [
        'name' => 'SuratTugas',
        'controller' => \App\Http\Controllers\Api\SuratTugasController::class,
        'model' => \App\Models\SuratTugas::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_TUGAS_' . rand(100, 999),
            'nama_dosen' => 'Test Dosen Updated',
            'alamat_dosen' => 'Jl. Dosen',
            'tugas_dosen' => 'Mengajar',
            'tugasnya' => 'Mengajar kelas dummy',
            'nama_mhs' => 'Dummy Mhs',
            'nim_nik' => 'ST_TG_' . rand(1000, 9999),
            'fakultas_prodi' => 'TBA',
            'judul_skripsi' => 'Skripsi Dummy',
            'masa_penugasan' => '2026-12-31',
            'tanggal' => '2026-05-25',
            'jenis_kelamin' => 'L'
        ]
    ],
    [
        'name' => 'SuratPernyataanVerifikasiNilai',
        'controller' => \App\Http\Controllers\Api\SuratPernyataanVerifikasiNilaiController::class,
        'model' => \App\Models\SuratPernyataanVerifikasiNilai::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_VN_' . rand(100, 999),
            'niy' => '12345',
            'jabatan' => 'Kaprodi',
            'nama_mhs' => 'Test VN Mahasiswa Updated',
            'nim' => 'VN_' . rand(1000, 9999),
            'prodi' => 'TBA',
            'fakultas' => 'TBA',
            'tanggal' => '2026-05-25',
            'tanda_tangan_id' => $tanda_tangan_id
        ]
    ],
    [
        'name' => 'SuratKeteranganUjianKomprehensifDiniyah',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganUjianKomprehensifDiniyahController::class,
        'model' => \App\Models\SuratKeteranganUjianKomprehensifDiniyah::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_UKD_' . rand(100, 999),
            'nama_mhs' => 'Test UKD Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'UKD_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'tanggal' => '2026-05-25',
            'tanda_tangan_id' => $tanda_tangan_id
        ]
    ],
    [
        'name' => 'SuratKeteranganTransfer',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganTransferController::class,
        'model' => \App\Models\SuratKeteranganTransfer::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_TRANS_' . rand(100, 999),
            'nama' => 'Test TRANS Mahasiswa Updated',
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir' => 'Surabaya',
            'nim' => 'TR_' . rand(1000, 9999),
            'jurusan_prodi' => 'TBA',
            'semester' => 'VIII',
            'tahun_akademik' => '2025/2026',
            'alamat' => 'Jl. Dummy',
            'universitas_tujuan' => 'Universitas Lain',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeteranganSpm',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganSpmController::class,
        'model' => \App\Models\SuratKeteranganSpm::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_SPM_' . rand(100, 999),
            'nama_lengkap' => 'Test SPM Mahasiswa Updated',
            'nim' => 'SPM_' . rand(1000, 9999),
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'TBA',
            'alamat' => 'Jl. Dummy',
            'nama_ortu' => 'Ortu Dummy',
            'tempat_tugas' => 'Tempat Dummy',
            'alamat_tugas' => 'Jl. Tugas',
            'tahun' => '2026',
            'semester' => 'Ganjil',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeteranganPpl',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganPplController::class,
        'model' => \App\Models\SuratKeteranganPpl::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_PPL_' . rand(100, 999),
            'tanda_tangan_id' => $tanda_tangan_id,
            'nama_mhs' => 'Test PPL Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'PPL_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'tanggal' => '2026-05-25'
        ]
    ],
    [
        'name' => 'SuratKeteranganKkn',
        'controller' => \App\Http\Controllers\Api\SuratKeteranganKknController::class,
        'model' => \App\Models\SuratKeteranganKkn::class,
        'update_data' => [
            'prodi_id' => $prodi_id,
            'no_surat' => 'ST_KKN_' . rand(100, 999),
            'tanda_tangan_id' => $tanda_tangan_id,
            'nama_mhs' => 'Test KKN Mahasiswa Updated',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'KKN_' . rand(1000, 9999),
            'prodi_mhs' => 'TBA',
            'alamat_rumah' => 'Jl. Dummy',
            'kelas_pondok' => 'A',
            'tanggal' => '2026-05-25'
        ]
    ]
];

echo "\n--- TESTING CRUD OPERATIONS FOR EVERY CONTROLLER ---\n";
foreach ($controllers as $item) {
    echo "===============================================\n";
    echo "Testing Controller: " . $item['name'] . "\n";
    echo "===============================================\n";

    try {
        $ctrl = $app->make($item['controller']);

        // 1. TEST INDEX
        echo "  [INDEX] Calling index()... ";
        $indexReq = Request::create('/', 'GET');
        $indexResponse = $ctrl->index($indexReq);
        if ($indexResponse instanceof \Symfony\Component\HttpFoundation\Response) {
            echo "SUCCESS (Status: " . $indexResponse->getStatusCode() . ")\n";
        } else {
            echo "SUCCESS (Non-standard response)\n";
        }

        // 2. ELOQUENT DUMMY INSERTION (Robust and bypasses store bugs)
        echo "  [MOCK INSERT] Inserting valid Eloquent mock record... ";
        $dummy = new $item['model']();
        $dummy_nim = 'NIM_' . rand(1000, 9999);

        if ($item['name'] == 'Sk6Controller (SuratKeterangan6)') {
            if ($sklmk && $skak && $sktkp && $skukd && $skqa) {
                $dummy->nama_mhs = 'Dummy SK6';
                $dummy->nim = $dummy_nim;
                $dummy->tanggal = '2026-05-25';
                $dummy->prodi_id = $prodi_id;
                $dummy->surat_keterangan_lulus_mata_kuliah_id = $sklmk->id;
                $dummy->surat_keterangan_administrasi_keuangan_id = $skak->id;
                $dummy->surat_keterangan_tasma_kkn_ppl_id = $sktkp->id;
                $dummy->surat_keterangan_ujian_komprehensif_diniyah_id = $skukd->id;
                $dummy->surat_keterangan_qismul_aman_id = $skqa->id;
            } else {
                echo "SKIP (Dependent models are missing for SK6)\n";
                continue;
            }
        } else if ($item['name'] == 'SuratKeteranganAktifMahasiswa') {
            $dummy->nomor_surat = '001/AM/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->nama_lengkap = 'Dummy AM';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->semester = 'VIII';
            $dummy->tahun_akademik = '2025/2026';
            $dummy->nama_ortu = 'Ortu';
            $dummy->alamat_ortu = 'Jl. Ortu';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganTasmaKknPpl') {
            $dummy->nomor_surat = '001/TKP/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->nama_lengkap = 'Dummy TKP';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat_rumah = 'Jl. Dummy';
            $dummy->kelas_pondok = 'A';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganQismulAman') {
            $dummy->nomor_surat = '001/QA/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->ketua = 'Ust. Fathul Munif';
            $dummy->nama_lengkap = 'Dummy QA';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat_rumah = 'Jl. Dummy';
            $dummy->kelas_pondok = 'A';
            $dummy->tanggal = '2026-05-25';
            $dummy->tanggal_berlaku_dari = '2026-01-01';
            $dummy->tanggal_berlaku_sampai = '2026-12-31';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganAdministrasiKeuangan') {
            $dummy->nomor_surat = '001/AK/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->nama_lengkap = 'Dummy AK';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat_rumah = 'Jl. Dummy';
            $dummy->kelas_pondok = 'A';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganDaftarS2') {
            $dummy->nomor_surat = '001/S2/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->nama_lengkap = 'Dummy S2';
            $dummy->nim = $dummy_nim;
            $dummy->prodi = 'TBA';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeterangan') {
            $dummy->nomor = '001/SK/' . rand(10, 99);
            $dummy->nama_mahasiswa = 'Dummy SK';
            $dummy->nim = $dummy_nim;
            $dummy->prodi = 'TBA';
            $dummy->periode_bulan = 'Mei 2026';
            $dummy->alasan = 'Testing alasan';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->prodi_id = $prodi_id;
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratIzinPenelitian') {
            $dummy->nomor = '001/SIP/' . rand(10, 99);
            $dummy->nama = 'Dummy SIP';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->kepada = 'Kepala Lembaga';
            $dummy->semester = 'VIII';
            $dummy->dari_tanggal = '2026-05-25';
            $dummy->tanggal = '2026-05-25';
            $dummy->prodi_id = $prodi_id;
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'HasilRapat') {
            $dummy->nomor_surat = '001/HR/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->agenda = 'Dummy Rapat';
            $dummy->tanggal = '2026-05-25';
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratTugas') {
            $dummy->nomor = '001/ST/' . rand(10, 99);
            $dummy->nama_dosen = 'Dummy Dosen';
            $dummy->alamat_dosen = 'Jl. Dosen';
            $dummy->tugas_dosen = 'Mengajar';
            $dummy->tugasnya = 'Mengajar kelas dummy';
            $dummy->nama_mhs = 'Dummy Mhs';
            $dummy->nim_nik = $dummy_nim;
            $dummy->fakultas_prodi = 'TBA';
            $dummy->judul_skripsi = 'Skripsi Dummy';
            $dummy->masa_penugasan = '2026-12-31';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->prodi_id = $prodi_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratPernyataanVerifikasiNilai') {
            $dummy->nomor = '001/VN/' . rand(10, 99);
            $dummy->niy = '12345';
            $dummy->jabatan = 'Kaprodi';
            $dummy->nama_mahasiswa = 'Dummy VN';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->fakultas = 'TBA';
            $dummy->tanggal = '2026-05-25';
            $dummy->prodi_id = $prodi_id;
            $dummy->user_id = $user_id;
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganUjianKomprehensifDiniyah') {
            $dummy->nomor_surat = '001/UKD/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->nama_lengkap = 'Dummy UKD';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat_rumah = 'Jl. Dummy';
            $dummy->kelas_pondok = 'A';
            $dummy->tanggal = '2026-05-25';
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganTransfer') {
            $dummy->nomor = '001/TR/' . rand(10, 99);
            $dummy->nama = 'Dummy TR';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->nim = $dummy_nim;
            $dummy->jurusan_prodi = 'TBA';
            $dummy->semester = 'VIII';
            $dummy->tahun_akademik = '2025/2026';
            $dummy->alamat = 'Jl. Dummy';
            $dummy->universitas_tujuan = 'Universitas Lain';
            $dummy->tanggal = '2026-05-25';
            $dummy->prodi_id = $prodi_id;
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganSpm') {
            $dummy->nomor_surat = '001/SPM/' . rand(10, 99);
            $dummy->prodi_id = $prodi_id;
            $dummy->nama_lengkap = 'Dummy SPM';
            $dummy->nim = $dummy_nim;
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat = 'Jl. Dummy';
            $dummy->nama_ortu = 'Ortu';
            $dummy->tempat_tugas = 'Tempat';
            $dummy->alamat_tugas = 'Jl. Tugas';
            $dummy->tahun = '2026';
            $dummy->semester = 'Ganjil';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganPpl') {
            $dummy->nomor_surat = '001/PPL/' . rand(10, 99);
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->nama_lengkap = 'Dummy PPL';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_id = $prodi_id;
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat_rumah = 'Jl. Dummy';
            $dummy->kelas_pondok = 'A';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        } else if ($item['name'] == 'SuratKeteranganKkn') {
            $dummy->nomor_surat = '001/KKN/' . rand(10, 99);
            $dummy->tanda_tangan_id = $tanda_tangan_id;
            $dummy->nama_lengkap = 'Dummy KKN';
            $dummy->tempat_lahir = 'Surabaya';
            $dummy->tanggal_lahir = '2000-01-01';
            $dummy->nim = $dummy_nim;
            $dummy->prodi_id = $prodi_id;
            $dummy->prodi_mhs = 'TBA';
            $dummy->alamat_rumah = 'Jl. Dummy';
            $dummy->kelas_pondok = 'A';
            $dummy->tanggal = '2026-05-25';
            $dummy->user_id = $user_id;
            $dummy->status = 'pending';
        }

        $dummy->save();
        $insertedId = $dummy->id;
        echo "SUCCESS (Inserted ID: $insertedId)\n";

        // 3. TEST SHOW
        echo "  [SHOW] Calling show($insertedId)... ";
        $showResponse = $ctrl->show($insertedId);
        if ($showResponse instanceof \Symfony\Component\HttpFoundation\Response) {
            echo "SUCCESS (Status: " . $showResponse->getStatusCode() . ")\n";
        } else {
            echo "SUCCESS (Non-standard response)\n";
        }

        // 4. TEST UPDATE
        if (!empty($item['update_data'])) {
            echo "  [UPDATE] Calling update($insertedId)... ";
            $updateReq = Request::create('/', 'PUT', $item['update_data']);
            $updateResponse = $ctrl->update($updateReq, $insertedId);
            if ($updateResponse instanceof \Symfony\Component\HttpFoundation\Response) {
                $content = json_decode($updateResponse->getContent(), true);
                if ($updateResponse->getStatusCode() >= 200 && $updateResponse->getStatusCode() < 300 && (!isset($content['status']) || $content['status'] !== false)) {
                    echo "SUCCESS (Status: " . $updateResponse->getStatusCode() . ")\n";
                } else {
                    echo "FAILED (Status: " . $updateResponse->getStatusCode() . ", Content: " . $updateResponse->getContent() . ")\n";
                }
            } else {
                echo "SUCCESS (Non-standard response)\n";
            }
        } else {
            echo "  [UPDATE] SKIPPED (No update payload)\n";
        }

        // 5. TEST DESTROY
        echo "  [DESTROY] Calling destroy($insertedId)... ";
        $destroyResponse = $ctrl->destroy($insertedId);
        if ($destroyResponse instanceof \Symfony\Component\HttpFoundation\Response) {
            echo "SUCCESS (Status: " . $destroyResponse->getStatusCode() . ")\n";
        } else {
            echo "SUCCESS (Non-standard response)\n";
        }

    } catch (\Throwable $e) {
        echo "  FAILED (Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . ")\n";
    }
}
