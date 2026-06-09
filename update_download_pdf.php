<?php
$files = [
    "HasilRapatController.php" => "HasilRapat",
    "Sk6Controller.php" => "SuratKeterangan6",
    "SuratKeteranganAdministrasiKeuanganController.php" => "SuratKeteranganAdministrasiKeuangan",
    "SuratKeteranganLulusMataKuliahController.php" => "SuratKeteranganLulusMataKuliah",
    "SuratKeteranganPplController.php" => "SuratKeteranganPpl",
    "SuratKeteranganQismulAmanController.php" => "SuratKeteranganQismulAman",
    "SuratKeteranganTasmaKknPplController.php" => "SuratKeteranganTasmaKknPpl",
    "SuratKeteranganUjianKomprehensifDiniyahController.php" => "SuratKeteranganUjianKomprehensifDiniyah"
];

$base = "c:/laragon/www/staff/back-end/staff.app/app/Http/Controllers/Api/";

foreach ($files as $file => $model) {
    $path = $base . $file;
    $content = file_get_contents($path);
    
    $startPos = strpos($content, "public function downloadPdf");
    if ($startPos === false) {
        echo "Error: public function downloadPdf not found in $file\n";
        continue;
    }
    
    $braceCount = 0;
    $insideFunction = false;
    $endPos = -1;
    
    for ($i = $startPos; $i < strlen($content); $i++) {
        if ($content[$i] === '{') {
            $braceCount++;
            $insideFunction = true;
        } elseif ($content[$i] === '}') {
            $braceCount--;
        }
        
        if ($insideFunction && $braceCount === 0) {
            $endPos = $i + 1;
            break;
        }
    }
    
    if ($endPos !== -1) {
        $replacement = "public function downloadPdf(\$id)
    {
        try {
            \$data = $model::find(\$id);

            if (!\$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (empty(\$data->local_path) || !file_exists(\$data->local_path)) {
                return response()->json(['status' => false, 'message' => 'File PDF tidak ditemukan di server'], 404);
            }

            \$fileName = basename(\$data->local_path);

            return response()->file(\$data->local_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename=\"' . \$fileName . '\"'
            ]);
        } catch (\Throwable \$th) {
            \Illuminate\Support\Facades\Log::error(\$th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }";
        $newContent = substr_replace($content, $replacement, $startPos, $endPos - $startPos);
        file_put_contents($path, $newContent);
        echo "Updated $file\n";
    } else {
        echo "Error: Could not find end of downloadPdf in $file\n";
    }
}
