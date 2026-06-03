<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileManagerService;
use App\Actions\DownloadProdiZipAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class FileManagerController extends Controller
{
    protected $fileManagerService;
    protected $downloadProdiZipAction;

    /**
     * Menggunakan Dependency Injection untuk memasukkan Service dan Action Class.
     * Menerapkan prinsip SOLID secara penuh.
     */
    public function __construct(
        FileManagerService $fileManagerService,
        DownloadProdiZipAction $downloadProdiZipAction
    ) {
        $this->fileManagerService = $fileManagerService;
        $this->downloadProdiZipAction = $downloadProdiZipAction;
    }

    /**
     * Endpoint API untuk melihat isi folder secara rekursif dan dinamis.
     * Terintegrasi dengan otorisasi Program Studi user yang sedang login.
     *
     * GET /api/file-manager/list
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $basePath = base_path('../public_html/pdf');

            // Cek Otorisasi Prodi:
            // Jika user memiliki prodi_id, maka dibatasi hanya pada folder prodi miliknya.
            // Jika user adalah BAAK/Admin (prodi_id null), maka dapat melihat keseluruhan folder prodi di dalam pdf.
            if ($user->prodi_id && $user->prodi) {
                $userRootPath = $basePath . '/' . $user->prodi->nama;
            } else {
                $userRootPath = $basePath;
            }

            // Pastikan direktori root user tersedia di penyimpanan lokal
            if (!File::exists($userRootPath)) {
                File::makeDirectory($userRootPath, 0755, true);
            }

            $subPath = $request->query('path', '');
            
            // Panggil logika service untuk mendapatkan file dan folder
            $contents = $this->fileManagerService->listContents($userRootPath, $subPath);

            return response()->json([
                'status' => true,
                'data' => [
                    'current_path' => str_replace('\\', '/', $subPath),
                    'folders' => $contents['folders'],
                    'files' => $contents['files'],
                    'user_prodi' => $user->prodi ? $user->prodi->nama : null
                ],
                'message' => 'Daftar berkas berhasil diambil.'
            ]);
        } catch (\Throwable $th) {
            Log::error('FileManager Error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Endpoint API untuk mengunduh seluruh isi folder tertentu dalam bentuk berkas ZIP.
     *
     * GET /api/file-manager/download-zip
     */
    public function downloadZip(Request $request)
    {
        try {
            $user = Auth::user();
            $basePath = base_path('../public_html/pdf');

            // Terapkan batas otorisasi path yang sama dengan fungsi index
            if ($user->prodi_id && $user->prodi) {
                $userRootPath = $basePath . '/' . $user->prodi->nama;
            } else {
                $userRootPath = $basePath;
            }

            $subPath = $request->query('path', '');

            // Validasi Keamanan: realpath mencegah Directory Traversal
            $realRoot = realpath($userRootPath);
            if (!$realRoot) {
                return response()->json([
                    'status' => false,
                    'message' => 'Direktori utama tidak ditemukan.'
                ], 404);
            }

            $targetPath = realpath($realRoot . '/' . $subPath);

            // Pastikan targetPath valid, berada di dalam realRoot, dan merupakan direktori
            if (!$targetPath || strpos($targetPath, $realRoot) !== 0 || !is_dir($targetPath)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akses ditolak atau direktori tidak ditemukan.'
                ], 403);
            }

            // Panggil Action Class untuk mengompresi folder
            $zipPath = $this->downloadProdiZipAction->execute($targetPath);

            // Mengambil nama folder untuk dijadikan nama file ZIP
            $folderName = basename($targetPath);
            $downloadName = $folderName . '.zip';

            // Kirim respon download dan hapus file temporer otomatis setelah dikirim
            return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);

        } catch (\Throwable $th) {
            Log::error('FileManager ZIP Error: ' . $th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengunduh ZIP: ' . $th->getMessage()
            ], 400);
        }
    }
}
