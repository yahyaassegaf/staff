<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class FileManagerService
{
    /**
     * Pemetaan nama controller ke kelas Model Eloquent masing-masing.
     * Digunakan untuk mencocokkan file PDF fisik ke database demi mengambil metadata.
     */
    private $modelMap = [
        'Sk6Controller' => \App\Models\SuratKeterangan6::class,
        'SuratIzinPenelitianController' => \App\Models\SuratIzinPenelitian::class,
        'SuratKeteranganAdministrasiKeuanganController' => \App\Models\SuratKeteranganAdministrasiKeuangan::class,
        'SuratKeteranganAktifMahasiswaController' => \App\Models\SuratKeteranganAktifMahasiswa::class,
        'SuratKeteranganController' => \App\Models\SuratKeterangan::class,
        'SuratKeteranganDaftarS2Controller' => \App\Models\SuratKeteranganDaftarS2::class,
        'SuratKeteranganKknController' => \App\Models\SuratKeteranganKkn::class,
        'SuratKeteranganLulusMataKuliahController' => \App\Models\SuratKeteranganLulusMataKuliah::class,
        'SuratKeteranganPplController' => \App\Models\SuratKeteranganPpl::class,
        'SuratKeteranganQismulAmanController' => \App\Models\SuratKeteranganQismulAman::class,
        'SuratKeteranganSpmController' => \App\Models\SuratKeteranganSpm::class,
        'SuratKeteranganTasmaKknPplController' => \App\Models\SuratKeteranganTasmaKknPpl::class,
        'SuratKeteranganTransferController' => \App\Models\SuratKeteranganTransfer::class,
        'SuratKeteranganUjianKomprehensifDiniyahController' => \App\Models\SuratKeteranganUjianKomprehensifDiniyah::class,
        'SuratPernyataanVerifikasiNilaiController' => \App\Models\SuratPernyataanVerifikasiNilai::class,
        'SuratTugasController' => \App\Models\SuratTugas::class,
    ];

    /**
     * Mengambil daftar isi (file & folder) dari direktori secara aman.
     *
     * @param string $userRootPath Path absolut direktori utama yang diperbolehkan untuk user.
     * @param string $subPath Path relatif subdirektori yang diminta.
     * @return array
     */
    public function listContents(string $userRootPath, string $subPath): array
    {
        $realRoot = realpath($userRootPath);
        if (!$realRoot) {
            throw new RuntimeException("Direktori root tidak ditemukan.");
        }

        // Menggabungkan path root dengan subpath dan menyelesaikan path absolut
        $targetPath = realpath($realRoot . '/' . $subPath);

        // Validasi keamanan: Mencegah celah Directory Traversal
        if (!$targetPath || strpos($targetPath, $realRoot) !== 0) {
            throw new RuntimeException("Akses ditolak: Percobaan Directory Traversal.");
        }

        $directories = [];
        $files = [];

        // Scan folder
        $dirs = File::directories($targetPath);
        foreach ($dirs as $dir) {
            $name = basename($dir);
            // Dapatkan path relatif dari realRoot
            $relPath = substr($dir, strlen($realRoot) + 1);
            $relPath = str_replace(DIRECTORY_SEPARATOR, '/', $relPath);

            $directories[] = [
                'name' => $name,
                'type' => 'folder',
                'path' => $relPath,
            ];
        }

        // Scan file
        $parts = explode('/', str_replace('\\', '/', $subPath));
        $controllerName = end($parts);
        $isControllerFolder = isset($this->modelMap[$controllerName]);

        $allFiles = File::files($targetPath);
        foreach ($allFiles as $file) {
            $name = $file->getFilename();
            // Hanya tampilkan file berekstensi PDF
            if (strtolower($file->getExtension()) !== 'pdf') {
                continue;
            }

            $filePath = $file->getRealPath();

            // Dapatkan informasi record dari database dan status keaktifan file
            $recordInfo = $this->getFileRecordInfo($filePath);

            // Jika berada di folder controller, filter file fisik usang (orphaned)
            if ($isControllerFolder) {
                if (!$recordInfo || !$recordInfo['is_active']) {
                    continue;
                }
            }

            $relPath = substr($filePath, strlen($realRoot) + 1);
            $relPath = str_replace(DIRECTORY_SEPARATOR, '/', $relPath);

            // Konstruksi URL Publik untuk file fisik lokal
            $pdfRoot = realpath(base_path('../public_html/pdf'));
            $publicRelPath = substr($filePath, strlen($pdfRoot) + 1);
            $publicRelPath = str_replace(DIRECTORY_SEPARATOR, '/', $publicRelPath);
            $publicUrl = url('pdf/' . $publicRelPath);

            $driveLink = $recordInfo ? $recordInfo['drive_link'] : null;

            $files[] = [
                'name' => $name,
                'type' => 'file',
                'path' => $relPath,
                'size' => $this->formatBytes($file->getSize()),
                'url' => $publicUrl,
                'drive_link' => $driveLink ?: null,
            ];
        }

        return [
            'folders' => $directories,
            'files' => $files,
        ];
    }

    /**
     * Mendapatkan informasi record database terkait file fisik dan mengecek keaktifannya.
     *
     * @param string $absoluteFilePath Path absolut file PDF fisik.
     * @return array|null
     */
    private function getFileRecordInfo(string $absoluteFilePath): ?array
    {
        $filePath = str_replace('\\', '/', $absoluteFilePath);
        $parts = explode('/', $filePath);

        if (count($parts) < 3) {
            return null;
        }

        $fileName = end($parts);
        $controllerName = $parts[count($parts) - 2];

        if (isset($this->modelMap[$controllerName])) {
            $modelClass = $this->modelMap[$controllerName];
            try {
                // Cari record berdasarkan kesamaan nama file di kolom local_path
                $record = $modelClass::where('local_path', 'like', '%' . $fileName)->first();
                if ($record) {
                    $isActive = false;
                    if ($record->local_path) {
                        $activeFileName = basename(str_replace('\\', '/', $record->local_path));
                        $isActive = ($activeFileName === $fileName);
                    }

                    return [
                        'drive_link' => $record->drive_link,
                        'is_active' => $isActive,
                    ];
                }
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Memformat ukuran file bytes ke ukuran manusiawi.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
