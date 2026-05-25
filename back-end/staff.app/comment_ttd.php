<?php
$files = glob('c:/laragon/www/staff/back-end/staff.app/resources/views/pdf/*.blade.php');
foreach($files as $file) {
    $content = file_get_contents($file);
    // Comment out stempel
    $content = preg_replace('/(background-image:\s*url\(\s*\'\{\{\s*\$stempel\s*\}\}\'\s*\);)/', '/* $1 */', $content);
    $content = preg_replace('/(@if\(\!empty\(\$stempel\)\)\s*background-image:\s*url\(\s*\'\{\{\s*\$stempel\s*\}\}\'\s*\);\s*@endif)/', '/* $1 */', $content);
    
    // Comment out TTD and Pengawas TTD
    $content = preg_replace('/(<img[^>]*src=\"\{\{\s*\$ttd\s*\}\}\"[^>]*>)/', '<!-- $1 -->', $content);
    $content = preg_replace('/(<img[^>]*src=\"\{\{\s*\$pengawas_ttd\s*\}\}\"[^>]*>)/', '<!-- $1 -->', $content);
    
    file_put_contents($file, $content);
}
echo "Done";
