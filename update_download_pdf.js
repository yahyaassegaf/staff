const fs = require('fs');
const path = require('path');

const files = {
    "HasilRapatController.php": "HasilRapat",
    "Sk6Controller.php": "SuratKeterangan6",
    "SuratKeteranganAdministrasiKeuanganController.php": "SuratKeteranganAdministrasiKeuangan",
    "SuratKeteranganLulusMataKuliahController.php": "SuratKeteranganLulusMataKuliah",
    "SuratKeteranganPplController.php": "SuratKeteranganPpl",
    "SuratKeteranganQismulAmanController.php": "SuratKeteranganQismulAman",
    "SuratKeteranganTasmaKknPplController.php": "SuratKeteranganTasmaKknPpl",
    "SuratKeteranganUjianKomprehensifDiniyahController.php": "SuratKeteranganUjianKomprehensifDiniyah"
};

const base = "c:/laragon/www/staff/back-end/staff.app/app/Http/Controllers/Api/";

for (const [file, model] of Object.entries(files)) {
    const filePath = path.join(base, file);
    if (!fs.existsSync(filePath)) {
        console.error(`File not found: ${filePath}`);
        continue;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');
    
    const startPos = content.indexOf("public function downloadPdf");
    if (startPos === -1) {
        console.error(`Error: public function downloadPdf not found in ${file}`);
        continue;
    }
    
    let braceCount = 0;
    let insideFunction = false;
    let endPos = -1;
    
    for (let i = startPos; i < content.length; i++) {
        if (content[i] === '{') {
            braceCount++;
            insideFunction = true;
        } else if (content[i] === '}') {
            braceCount--;
        }
        
        if (insideFunction && braceCount === 0) {
            endPos = i + 1;
            break;
        }
    }
    
    if (endPos !== -1) {
        const replacement = `public function downloadPdf($id)
    {
        try {
            $data = ${model}::find($id);

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (empty($data->local_path) || !file_exists($data->local_path)) {
                return response()->json(['status' => false, 'message' => 'File PDF tidak ditemukan di server'], 404);
            }

            $fileName = basename($data->local_path);

            return response()->file($data->local_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\\Throwable $th) {
            \\Illuminate\\Support\\Facades\\Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }`;
        content = content.substring(0, startPos) + replacement + content.substring(endPos);
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Updated ${file}`);
    } else {
        console.error(`Error: Could not find end of downloadPdf in ${file}`);
    }
}
