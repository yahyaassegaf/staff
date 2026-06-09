$files = @(
    @("HasilRapatController.php", "HasilRapat"),
    @("Sk6Controller.php", "SuratKeterangan6"),
    @("SuratKeteranganAdministrasiKeuanganController.php", "SuratKeteranganAdministrasiKeuangan"),
    @("SuratKeteranganLulusMataKuliahController.php", "SuratKeteranganLulusMataKuliah"),
    @("SuratKeteranganPplController.php", "SuratKeteranganPpl"),
    @("SuratKeteranganQismulAmanController.php", "SuratKeteranganQismulAman"),
    @("SuratKeteranganTasmaKknPplController.php", "SuratKeteranganTasmaKknPpl"),
    @("SuratKeteranganUjianKomprehensifDiniyahController.php", "SuratKeteranganUjianKomprehensifDiniyah")
)

$base = "c:\laragon\www\staff\back-end\staff.app\app\Http\Controllers\Api\"

foreach ($item in $files) {
    $file = $item[0]
    $model = $item[1]
    $path = $base + $file
    
    $content = [System.IO.File]::ReadAllText($path)
    
    $startPos = $content.IndexOf("public function downloadPdf")
    if ($startPos -lt 0) { continue }
    
    $braceCount = 0
    $inside = $false
    $endPos = -1
    
    for ($i = $startPos; $i -lt $content.Length; $i++) {
        if ($content[$i] -eq '{') {
            $braceCount++
            $inside = $true
        } elseif ($content[$i] -eq '}') {
            $braceCount--
        }
        
        if ($inside -and $braceCount -eq 0) {
            $endPos = $i + 1
            break
        }
    }
    
    if ($endPos -gt -1) {
        $replacement = @"
public function downloadPdf(`$id)
    {
        try {
            `$data = ${model}::find(`$id);

            if (!`$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (empty(`$data->local_path) || !file_exists(`$data->local_path)) {
                return response()->json(['status' => false, 'message' => 'File PDF tidak ditemukan di server'], 404);
            }

            `$fileName = basename(`$data->local_path);

            return response()->file(`$data->local_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . `$fileName . '"'
            ]);
        } catch (\Throwable `$th) {
            \Illuminate\Support\Facades\Log::error(`$th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }
"@
        $newContent = $content.Substring(0, $startPos) + $replacement + $content.Substring($endPos)
        [System.IO.File]::WriteAllText($path, $newContent)
        Write-Host "Updated $file"
    }
}
