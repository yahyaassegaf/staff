<?php

namespace App\Actions;

use ZipArchive;
use RuntimeException;

class DownloadProdiZipAction
{
    /**
     * Membuat file zip dari sebuah direktori secara rekursif.
     *
     * @param string $sourceDir Direktori fisik yang ingin dikompresi.
     * @return string Path ke file ZIP temporer yang dihasilkan.
     */
    public function execute(string $sourceDir): string
    {
        if (!is_dir($sourceDir)) {
            throw new RuntimeException("Direktori tidak ditemukan: " . $sourceDir);
        }

        // Membuat file temporer unik
        $tempZip = tempnam(sys_get_temp_dir(), 'prodi_zip_');
        $zipPath = $tempZip . '.zip';
        rename($tempZip, $zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Tidak dapat membuat file ZIP di " . $zipPath);
        }

        $sourceDir = rtrim($sourceDir, '/\\');
        
        // Membaca file secara rekursif
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $hasFiles = false;
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                // Dapatkan relative path dari folder source
                $relativePath = substr($filePath, strlen($sourceDir) + 1);

                // Tambahkan file ke dalam arsip ZIP
                $zip->addFile($filePath, $relativePath);
                $hasFiles = true;
            }
        }

        // Jika direktori kosong, tambahkan file kosong sebagai penanda
        if (!$hasFiles) {
            $zip->addFromString('kosong.txt', 'Folder ini tidak memiliki file PDF.');
        }

        $zip->close();

        return $zipPath;
    }
}
