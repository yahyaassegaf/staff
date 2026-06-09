<?php
$dir = __DIR__ . '/../resources/views/pdf';
$files = [
    'surat_izin_penelitian.blade.php',
    'surat_keterangan_daftar_s2.blade.php',
    'kkn.blade.php',
    'surat_keterangan_spm.blade.php',
    'surat_keterangan_transfer.blade.php',
    'surat_pernyataan_verifikasi_nilai.blade.php',
    'surat_tugas.blade.php'
];

$count = 0;
foreach ($files as $file) {
    $path = $dir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $original = $content;

        // Change background-position for the stamp overlay
        $content = preg_replace('/background-position:\s*\d+%\s*50%;/', 'background-position: 10px center;', $content);

        // Change the width and margin-left of the signature image
        $content = preg_replace('/<img\s+src="\{\{\s*\$ttd\s*\}\}"\s+style="width:\s*\d+px;\s*">/i', '<img src="{{ $ttd }}" style="width: 200px; margin-left: 70px;">', $content);
        $content = preg_replace('/<img\s+src="\{\{\s*\$ttd\s*\}\}"\s+style="width:\s*\d+px;">/i', '<img src="{{ $ttd }}" style="width: 200px; margin-left: 70px;">', $content);

        if ($content !== $original) {
            file_put_contents($path, $content);
            $count++;
            echo "Updated $file<br>";
        } else {
            echo "No changes for $file<br>";
        }
    } else {
        echo "File not found: $file<br>";
    }
}
echo "Total updated: $count<br>";
