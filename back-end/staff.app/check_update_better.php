<?php
$dir = __DIR__ . '/app/Http/Controllers/Api';
$files = glob($dir . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Check if it has an update method
    if (strpos($content, 'public function update(') !== false) {
        // Find the start of update
        $start = strpos($content, 'public function update(');
        // Find the next public function to approximate the end
        $end = strpos($content, 'public function ', $start + 20);
        if ($end === false) $end = strlen($content);
        
        $updateBody = substr($content, $start, $end - $start);
        
        // If it validates alamat or alamat_rumah
        if (preg_match("/'alamat(?:_rumah)?'\s*=>/", $updateBody)) {
            // Does it assign it?
            if (!preg_match('/->(?:alamat|alamat_rumah)\s*=/', $updateBody)) {
                echo basename($file) . " MISSING assignment!\n";
            }
        }
    }
}
