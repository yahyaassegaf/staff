<?php
$dir = __DIR__ . '/app/Http/Controllers/Api';
$files = glob($dir . '/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/public function update\s*\([^)]*\)\s*{(.*?)}/s', $content, $m)) {
        if (preg_match('/alamat(?:_rumah)?/', $m[1])) {
            echo basename($file) . ":\n";
            preg_match_all('/(?:\\$[a-zA-Z0-9_]+->)?(?:alamat|alamat_rumah|kelas_pondok|prodi_mhs|tempat_lahir|tanggal_lahir|nim|nama_lengkap|nama_mhs|nama_dosen|alamat_dosen)\s*=\s*(.*?);/', $m[1], $vars);
            foreach ($vars[0] as $v) {
                echo "  $v\n";
            }
        }
    }
}
