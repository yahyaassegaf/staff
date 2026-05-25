const fs = require('fs');

const controllers = [
    'SuratIzinPenelitianController.php',
    'SuratKeteranganAdministrasiKeuanganController.php',
    'SuratKeteranganAktifMahasiswaController.php',
    'SuratKeteranganController.php',
    'SuratKeteranganKknController.php',
    'SuratKeteranganLulusMataKuliahController.php',
    'SuratKeteranganPplController.php',
    'SuratKeteranganTasmaKknPplController.php',
    'SuratKeteranganUjianKomprehensifDiniyahController.php',
    'SuratPernyataanVerifikasiNilaiController.php',
    'SuratTugasController.php'
];

for (let c of controllers) {
    const filePath = 'c:/laragon/www/staff/back-end/staff.app/app/Http/Controllers/Api/' + c;
    if (fs.existsSync(filePath)) {
        let content = fs.readFileSync(filePath, 'utf8');

        // Regex to match the injected lines
        const regex = /((?: {12}|\t{3})\$[a-zA-Z0-9_]+\s*=\s*\\?App\\Services\\SuratService::(?:formatNomorSurat|NoSuratKeteranganTasmaKknPpl)\b.*?\n(?: {12}|\t{3})\$[a-zA-Z0-9_]+->(?:nomor|nomor_surat)\s*=\s*\$[a-zA-Z0-9_]+;\n\s*)((?: {12}|\t{3})\$[a-zA-Z0-9_]+\s*=\s*[a-zA-Z0-9_]+::find\(\$id\);\n(?: {12}|\t{3})if\s*\(!\$[a-zA-Z0-9_]+\)\s*\{\n(?:.*?\n){1,6}(?: {12}|\t{3})\}\n)/s;

        const match = content.match(regex);
        if (match) {
            // Swap group 1 (format/assign) with group 2 (find/if)
            const swapped = match[2] + '\n' + match[1];
            content = content.replace(regex, swapped);
            fs.writeFileSync(filePath, content, 'utf8');
            console.log('Fixed ' + c);
        } else {
            console.log('Skipped ' + c + ' (Already fixed or not matching)');
        }
    }
}
