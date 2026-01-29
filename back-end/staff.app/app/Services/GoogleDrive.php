<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Yaza\LaravelGoogleDriveStorage\Gdrive;

class GoogleDrive
{
    /**
     * Set permission file menjadi "anyone with link can view"
     * Agar file bisa diakses melalui link tanpa harus login
     */
    public static function setPublicPermission(string $fileId): bool
    {
        try {
            $client = new \Google_Client();
            $client->setClientId(config('filesystems.disks.google.clientId'));
            $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
            $client->addScope(\Google_Service_Drive::DRIVE);

            // Set credentials menggunakan refresh token
            $client->setAccessType('offline');
            $client->refreshToken(config('filesystems.disks.google.refreshToken'));

            $service = new \Google_Service_Drive($client);

            // Buat permission "anyone with link can view"
            $permission = new \Google_Service_Drive_Permission();
            $permission->setType('anyone');
            $permission->setRole('reader');

            $service->permissions->create($fileId, $permission);

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to set public permission for file {$fileId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Memastikan folder ada, jika belum ada maka dibuat.
     * Mengembalikan path folder.
     */
    public static function ensureFolder(string $path): string
    {
        // Cek apakah folder sudah ada
        $exists = false;
        try {
            $contents = Gdrive::all('/', true);
            foreach ($contents as $item) {
                if ($item->isDir() && $item->path() === $path) {
                    $exists = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            // Folder mungkin belum ada, lanjutkan ke pembuatan
        }

        // Jika belum ada, buat folder
        if (!$exists) {
            try {
                Gdrive::makeDir($path);
            } catch (\Exception $e) {
                // Mungkin folder sudah ada, abaikan error
            }
        }

        return $path;
    }

    /**
     * Upload file ke Google Drive dan dapatkan file ID
     * 
     * @return array{path: string, file_id: string|null}
     */
    public static function uploadFile(string $folderPath, string $localFilePath, string $fileName): array
    {
        $fullPath = rtrim($folderPath, '/') . '/' . $fileName;

        // Upload file
        Gdrive::put($fullPath, $localFilePath);

        // Cari file yang baru diupload untuk mendapatkan ID
        $fileId = null;
        try {
            $disk = Storage::disk('google');
            $contents = $disk->listContents($folderPath);
            foreach ($contents as $item) {
                if ($item['type'] === 'file' && $item['path'] === $fullPath) {
                    $fileId = $item['extraMetadata']['id'] ?? null;
                    break;
                }
            }
        } catch (\Exception $e) {
            // Gagal mendapatkan file ID, tapi file mungkin sudah terupload
        }

        return [
            'path' => $fullPath,
            'file_id' => $fileId
        ];
    }
}
