<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProdiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FakultasController;
use App\Http\Controllers\Api\SuratKeteranganLulusMataKuliahController;
use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\SuratKeteranganAdministrasiKeuanganController;
use App\Http\Controllers\Api\SuratKeteranganUjianKomprehensifDiniyahController;
use App\Http\Controllers\Api\SuratKeteranganTasmaKknPplController;
use App\Http\Controllers\Api\SuratKeteranganQismulAmanController;
use App\Http\Controllers\Api\SuratKeteranganAktifMahasiswaController;
use App\Http\Controllers\Api\SuratPernyataanVerifikasiNilaiController;
use App\Http\Controllers\Api\SuratKeteranganController;
use App\Http\Controllers\Api\SuratTugasController;
use App\Http\Controllers\Api\SuratKeteranganTransferController;
use App\Http\Controllers\Api\SuratIzinPenelitianController;
use App\Http\Controllers\Api\HasilRapatController;
use App\Http\Controllers\Api\TandaTanganController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SuratKeteranganKknController;
use App\Http\Controllers\Api\SuratKeteranganPplController;
use App\Http\Controllers\Api\TemplateIjazahController;
use App\Http\Controllers\Api\BatchController;
use App\Http\Controllers\Api\JenisSuratController;
use App\Http\Controllers\Api\SuratKeteranganSpmController;
use App\Http\Controllers\Api\SuratKeteranganDaftarS2Controller;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [LoginController::class, 'login'])->name('api.login');
Route::get('/profile', [LoginController::class, 'profile'])->middleware('auth:sanctum');
Route::put('/profile', [LoginController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/foto/{filename}', [LoginController::class, 'serveFoto'])->where('filename', '.*');


Route::get('/get-level', [LoginController::class, 'getLevel'])->middleware('auth:sanctum');
Route::get('/data-users', [LoginController::class, 'dataUsers'])->middleware('auth:sanctum');
Route::get('/data-users/{id}', [LoginController::class, 'show'])->middleware('auth:sanctum');
Route::put('/data-users/{id}', [LoginController::class, 'update'])->middleware('auth:sanctum');
Route::post('/data-users', [LoginController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/data-users/{id}', [LoginController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/get-prodi', [LoginController::class, 'getProdi'])->middleware('auth:sanctum');
Route::get('/get-all-prodi', [LoginController::class, 'getProdi'])->middleware('auth:sanctum');
Route::get('/get-all-tanda-tangan', [LoginController::class, 'getTandaTangan'])->middleware('auth:sanctum');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:sanctum');
Route::get('/dashboard/cards', [DashboardController::class, 'cards'])->middleware('auth:sanctum');

// Prodi Routes
Route::get('/prodi', [ProdiController::class, 'index'])->middleware('auth:sanctum');
Route::get('/prodi/{id}', [ProdiController::class, 'show'])->middleware('auth:sanctum');
Route::post('/prodi', [ProdiController::class, 'store'])->middleware('auth:sanctum');
Route::put('/prodi/{id}', [ProdiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/prodi/{id}', [ProdiController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/fakultas', [FakultasController::class, 'index'])->middleware('auth:sanctum');
Route::get('/fakultas/{id}', [FakultasController::class, 'show'])->middleware('auth:sanctum');
Route::post('/fakultas', [FakultasController::class, 'store'])->middleware('auth:sanctum');
Route::put('/fakultas/{id}', [FakultasController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/fakultas/{id}', [FakultasController::class, 'destroy'])->middleware('auth:sanctum');

Route::get('/sklmk', [SuratKeteranganLulusMataKuliahController::class, 'index'])->middleware('auth:sanctum');
Route::get('/sklmk/{id}', [SuratKeteranganLulusMataKuliahController::class, 'show'])->middleware('auth:sanctum');
Route::post('/sklmk', [SuratKeteranganLulusMataKuliahController::class, 'store'])->middleware('auth:sanctum');
Route::put('/sklmk/{id}', [SuratKeteranganLulusMataKuliahController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/sklmk/{id}', [SuratKeteranganLulusMataKuliahController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/get-prodi', [SuratKeteranganLulusMataKuliahController::class, 'getProdi'])->middleware('auth:sanctum');
// Route::get('/get-mhs', [MahasiswaController::class, 'search'])->middleware('auth:sanctum');
// Route::get('/get-mhss/{search}', [MahasiswaController::class, 'search']);

// Mahasiswa CRUD Routes
Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->middleware('auth:sanctum');
Route::get('/mahasiswa/{id}', [MahasiswaController::class, 'show'])->middleware('auth:sanctum');
Route::post('/mahasiswa', [MahasiswaController::class, 'store'])->middleware('auth:sanctum');
Route::put('/mahasiswa/{id}', [MahasiswaController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/mahasiswa/{id}', [MahasiswaController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/mahasiswa/import', [MahasiswaController::class, 'import'])->middleware('auth:sanctum');
Route::get('/get-mhs', [MahasiswaController::class, 'search'])->middleware('auth:sanctum');
Route::get('/download-pdf/{id}', [SuratKeteranganLulusMataKuliahController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Ujian Komprehensif Diniyah Routes
Route::get('/skukd', [SuratKeteranganUjianKomprehensifDiniyahController::class, 'index'])->middleware('auth:sanctum');
Route::get('/skukd/{id}', [SuratKeteranganUjianKomprehensifDiniyahController::class, 'show'])->middleware('auth:sanctum');
Route::post('/skukd', [SuratKeteranganUjianKomprehensifDiniyahController::class, 'store'])->middleware('auth:sanctum');
Route::put('/skukd/{id}', [SuratKeteranganUjianKomprehensifDiniyahController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skukd/{id}', [SuratKeteranganUjianKomprehensifDiniyahController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skukd/download-pdf/{id}', [SuratKeteranganUjianKomprehensifDiniyahController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Administrasi Keuangan Routes
Route::get('/skak', [SuratKeteranganAdministrasiKeuanganController::class, 'index'])->middleware('auth:sanctum');
Route::get('/skak/{id}', [SuratKeteranganAdministrasiKeuanganController::class, 'show'])->middleware('auth:sanctum');
Route::post('/skak', [SuratKeteranganAdministrasiKeuanganController::class, 'store'])->middleware('auth:sanctum');
Route::put('/skak/{id}', [SuratKeteranganAdministrasiKeuanganController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skak/{id}', [SuratKeteranganAdministrasiKeuanganController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skak/download-pdf/{id}', [SuratKeteranganAdministrasiKeuanganController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan TASMA, KKN, PPL Routes
Route::get('/sktkp', [SuratKeteranganTasmaKknPplController::class, 'index'])->middleware('auth:sanctum');
Route::get('/sktkp/{id}', [SuratKeteranganTasmaKknPplController::class, 'show'])->middleware('auth:sanctum');
Route::post('/sktkp', [SuratKeteranganTasmaKknPplController::class, 'store'])->middleware('auth:sanctum');
Route::put('/sktkp/{id}', [SuratKeteranganTasmaKknPplController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/sktkp/{id}', [SuratKeteranganTasmaKknPplController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/sktkp/download-pdf/{id}', [SuratKeteranganTasmaKknPplController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Qismul Aman Routes
Route::get('/skqa', [SuratKeteranganQismulAmanController::class, 'index'])->middleware('auth:sanctum');
Route::get('/skqa/{id}', [SuratKeteranganQismulAmanController::class, 'show'])->middleware('auth:sanctum');
Route::post('/skqa', [SuratKeteranganQismulAmanController::class, 'store'])->middleware('auth:sanctum');
Route::put('/skqa/{id}', [SuratKeteranganQismulAmanController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skqa/{id}', [SuratKeteranganQismulAmanController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skqa/download-pdf/{id}', [SuratKeteranganQismulAmanController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Aktif Mahasiswa Routes
Route::get('/skam', [SuratKeteranganAktifMahasiswaController::class, 'index'])->middleware('auth:sanctum');
Route::get('/skam/{id}', [SuratKeteranganAktifMahasiswaController::class, 'show'])->middleware('auth:sanctum');
Route::post('/skam', [SuratKeteranganAktifMahasiswaController::class, 'store'])->middleware('auth:sanctum');
Route::put('/skam/{id}', [SuratKeteranganAktifMahasiswaController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skam/{id}', [SuratKeteranganAktifMahasiswaController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skam/download-pdf/{id}', [SuratKeteranganAktifMahasiswaController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan SPM Routes
Route::get('/spm', [SuratKeteranganSpmController::class, 'index'])->middleware('auth:sanctum');
Route::get('/spm/{id}', [SuratKeteranganSpmController::class, 'show'])->middleware('auth:sanctum');
Route::post('/spm', [SuratKeteranganSpmController::class, 'store'])->middleware('auth:sanctum');
Route::put('/spm/{id}', [SuratKeteranganSpmController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/spm/{id}', [SuratKeteranganSpmController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/spm/download-pdf/{id}', [SuratKeteranganSpmController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Daftar S2 Routes
Route::get('/skds2', [SuratKeteranganDaftarS2Controller::class, 'index'])->middleware('auth:sanctum');
Route::get('/skds2/{id}', [SuratKeteranganDaftarS2Controller::class, 'show'])->middleware('auth:sanctum');
Route::post('/skds2', [SuratKeteranganDaftarS2Controller::class, 'store'])->middleware('auth:sanctum');
Route::put('/skds2/{id}', [SuratKeteranganDaftarS2Controller::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skds2/{id}', [SuratKeteranganDaftarS2Controller::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skds2/download-pdf/{id}', [SuratKeteranganDaftarS2Controller::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Pernyataan Verifikasi Nilai Routes
Route::get('/spvn', [SuratPernyataanVerifikasiNilaiController::class, 'index'])->middleware('auth:sanctum');
Route::get('/spvn/{id}', [SuratPernyataanVerifikasiNilaiController::class, 'show'])->middleware('auth:sanctum');
Route::post('/spvn', [SuratPernyataanVerifikasiNilaiController::class, 'store'])->middleware('auth:sanctum');
Route::put('/spvn/{id}', [SuratPernyataanVerifikasiNilaiController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/spvn/{id}', [SuratPernyataanVerifikasiNilaiController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/spvn/download-pdf/{id}', [SuratPernyataanVerifikasiNilaiController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Routes
Route::get('/surat-keterangan', [SuratKeteranganController::class, 'index'])->middleware('auth:sanctum');
Route::get('/surat-keterangan/{id}', [SuratKeteranganController::class, 'show'])->middleware('auth:sanctum');
Route::post('/surat-keterangan', [SuratKeteranganController::class, 'store'])->middleware('auth:sanctum');
Route::put('/surat-keterangan/{id}', [SuratKeteranganController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/surat-keterangan/{id}', [SuratKeteranganController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/surat-keterangan/download-pdf/{id}', [SuratKeteranganController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Tugas Routes
Route::get('/surat-tugas', [SuratTugasController::class, 'index'])->middleware('auth:sanctum');
Route::get('/surat-tugas/{id}', [SuratTugasController::class, 'show'])->middleware('auth:sanctum');
Route::post('/surat-tugas', [SuratTugasController::class, 'store'])->middleware('auth:sanctum');
Route::put('/surat-tugas/{id}', [SuratTugasController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/surat-tugas/{id}', [SuratTugasController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/surat-tugas/download-pdf/{id}', [SuratTugasController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan Transfer Routes
Route::get('/surat-keterangan-transfer', [SuratKeteranganTransferController::class, 'index'])->middleware('auth:sanctum');
Route::get('/surat-keterangan-transfer/{id}', [SuratKeteranganTransferController::class, 'show'])->middleware('auth:sanctum');
Route::post('/surat-keterangan-transfer', [SuratKeteranganTransferController::class, 'store'])->middleware('auth:sanctum');
Route::put('/surat-keterangan-transfer/{id}', [SuratKeteranganTransferController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/surat-keterangan-transfer/{id}', [SuratKeteranganTransferController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/surat-keterangan-transfer/download-pdf/{id}', [SuratKeteranganTransferController::class, 'downloadPdf'])->middleware('auth:sanctum');
Route::get('/get-th-akademik', [SuratKeteranganTransferController::class, 'getThAkademik'])->middleware('auth:sanctum');

// Surat Izin Penelitian Routes
Route::get('/surat-izin-penelitian', [SuratIzinPenelitianController::class, 'index'])->middleware('auth:sanctum');
Route::get('/surat-izin-penelitian/{id}', [SuratIzinPenelitianController::class, 'show'])->middleware('auth:sanctum');
Route::post('/surat-izin-penelitian', [SuratIzinPenelitianController::class, 'store'])->middleware('auth:sanctum');
Route::put('/surat-izin-penelitian/{id}', [SuratIzinPenelitianController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/surat-izin-penelitian/{id}', [SuratIzinPenelitianController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/surat-izin-penelitian/download-pdf/{id}', [SuratIzinPenelitianController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Hasil Rapat Routes
Route::get('/hasil-rapat', [HasilRapatController::class, 'index'])->middleware('auth:sanctum');
Route::get('/hasil-rapat/{id}', [HasilRapatController::class, 'show'])->middleware('auth:sanctum');
Route::post('/hasil-rapat', [HasilRapatController::class, 'store'])->middleware('auth:sanctum');
Route::put('/hasil-rapat/{id}', [HasilRapatController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/hasil-rapat/{id}', [HasilRapatController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/hasil-rapat/download-pdf/{id}', [HasilRapatController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Tanda Tangan Routes
Route::get('/tanda-tangan', [TandaTanganController::class, 'index'])->middleware('auth:sanctum');
Route::get('/tanda-tangan/{id}', [TandaTanganController::class, 'show'])->middleware('auth:sanctum');
Route::post('/tanda-tangan', [TandaTanganController::class, 'store'])->middleware('auth:sanctum');
Route::put('/tanda-tangan/{id}', [TandaTanganController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/tanda-tangan/{id}', [TandaTanganController::class, 'destroy'])->middleware('auth:sanctum');

// Surat Keterangan KKN Routes
Route::get('/skk', [SuratKeteranganKknController::class, 'index'])->middleware('auth:sanctum');
Route::get('/skk/{id}', [SuratKeteranganKknController::class, 'show'])->middleware('auth:sanctum');
Route::post('/skk', [SuratKeteranganKknController::class, 'store'])->middleware('auth:sanctum');
Route::put('/skk/{id}', [SuratKeteranganKknController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skk/{id}', [SuratKeteranganKknController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skk/download-pdf/{id}', [SuratKeteranganKknController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Surat Keterangan PPL Routes
Route::get('/skp', [SuratKeteranganPplController::class, 'index'])->middleware('auth:sanctum');
Route::get('/skp/{id}', [SuratKeteranganPplController::class, 'show'])->middleware('auth:sanctum');
Route::post('/skp', [SuratKeteranganPplController::class, 'store'])->middleware('auth:sanctum');
Route::put('/skp/{id}', [SuratKeteranganPplController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/skp/{id}', [SuratKeteranganPplController::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/skp/download-pdf/{id}', [SuratKeteranganPplController::class, 'downloadPdf'])->middleware('auth:sanctum');

// Template Ijazah Routes
Route::get('/template-ijazah', [TemplateIjazahController::class, 'index'])->middleware('auth:sanctum');
Route::get('/template-ijazah/{id}', [TemplateIjazahController::class, 'show'])->middleware('auth:sanctum');
Route::post('/template-ijazah', [TemplateIjazahController::class, 'store'])->middleware('auth:sanctum');
Route::put('/template-ijazah/{id}', [TemplateIjazahController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/template-ijazah/{id}', [TemplateIjazahController::class, 'destroy'])->middleware('auth:sanctum');

// Batch Routes
Route::get('/batch', [BatchController::class, 'index'])->middleware('auth:sanctum');
Route::get('/batch/{id}', [BatchController::class, 'show'])->middleware('auth:sanctum');
Route::post('/batch', [BatchController::class, 'store'])->middleware('auth:sanctum');
Route::put('/batch/{id}', [BatchController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/batch/{id}', [BatchController::class, 'destroy'])->middleware('auth:sanctum');

// Jenis Surat Routes
Route::get('/jenis-surat', [JenisSuratController::class, 'index'])->middleware('auth:sanctum');
Route::get('/jenis-surat/{id}', [JenisSuratController::class, 'show'])->middleware('auth:sanctum');
Route::post('/jenis-surat', [JenisSuratController::class, 'store'])->middleware('auth:sanctum');
Route::put('/jenis-surat/{id}', [JenisSuratController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/jenis-surat/{id}', [JenisSuratController::class, 'destroy'])->middleware('auth:sanctum');

// Setting Jabatan Routes
Route::get('/setting-jabatan', [\App\Http\Controllers\Api\SettingJabatanController::class, 'index'])->middleware('auth:sanctum');
Route::get('/setting-jabatan/{id}', [\App\Http\Controllers\Api\SettingJabatanController::class, 'show'])->middleware('auth:sanctum');
Route::post('/setting-jabatan', [\App\Http\Controllers\Api\SettingJabatanController::class, 'store'])->middleware('auth:sanctum');
Route::put('/setting-jabatan/{id}', [\App\Http\Controllers\Api\SettingJabatanController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/setting-jabatan/{id}', [\App\Http\Controllers\Api\SettingJabatanController::class, 'destroy'])->middleware('auth:sanctum');

// SK6 Routes
Route::get('/sk6', [\App\Http\Controllers\Api\Sk6Controller::class, 'index'])->middleware('auth:sanctum');
Route::get('/sk6/{id}', [\App\Http\Controllers\Api\Sk6Controller::class, 'show'])->middleware('auth:sanctum');
Route::post('/sk6', [\App\Http\Controllers\Api\Sk6Controller::class, 'store'])->middleware('auth:sanctum');
Route::put('/sk6/{id}', [\App\Http\Controllers\Api\Sk6Controller::class, 'update'])->middleware('auth:sanctum');
Route::delete('/sk6/{id}', [\App\Http\Controllers\Api\Sk6Controller::class, 'destroy'])->middleware('auth:sanctum');
Route::get('/sk6/download-pdf/{id}', [\App\Http\Controllers\Api\Sk6Controller::class, 'downloadPdf'])->middleware('auth:sanctum');

// File Manager Routes
Route::get('/file-manager/list', [\App\Http\Controllers\Api\FileManagerController::class, 'index'])->middleware('auth:sanctum');
Route::get('/file-manager/download-zip', [\App\Http\Controllers\Api\FileManagerController::class, 'downloadZip'])->middleware('auth:sanctum');
