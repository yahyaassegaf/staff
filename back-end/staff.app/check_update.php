<?php

$dir = __DIR__ . '/app/Http/Controllers/Api';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/public function update\s*\([^)]*\)\s*{(.*?)}/s', $content, $matches)) {
        $updateBody = $matches[1];
        // Only look at controllers that validate alamat or alamat_rumah
        if (preg_match("/'alamat(?:_rumah)?'\s*=>/", $updateBody)) {
            // Check if it assigns to the model
            if (!preg_match('/->alamat(?:_rumah)?\s*=/', $updateBody)) {
                echo basename($file) . " has validation but MISSING assignment in update!\n";
            }
        }
    }
}
