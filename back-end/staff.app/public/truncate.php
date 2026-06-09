<?php
$files = [
    'c:/laragon/www/staff/back-end/staff.app/resources/views/pdf/surat_izin_penelitian.blade.php',
    'c:/laragon/www/staff/back-end/staff.app/resources/views/pdf/kkn.blade.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Find the first </html> and truncate everything after it
    $pos = strpos($content, '</html>');
    if ($pos !== false) {
        $content = substr($content, 0, $pos + 7);
        file_put_contents($file, $content);
        echo "Truncated $file\n";
    }
}
