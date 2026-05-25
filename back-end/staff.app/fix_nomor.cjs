const fs = require('fs');
const path = require('path');

const controllersDir = 'c:/laragon/www/staff/back-end/staff.app/app/Http/Controllers/Api';

const files = fs.readdirSync(controllersDir);

files.forEach(file => {
    if (!file.endsWith('Controller.php')) return;
    
    let filePath = path.join(controllersDir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    // 1. Find the variable used for the combined formatted string.
    // Usually it is passed to SuratService::formatNomorSurat
    // e.g., $noSurat = \App\Services\SuratService::formatNomorSurat(...)
    // or $formattedNoSurat = SuratService::formatNomorSurat(...)
    
    let match = content.match(/\$([a-zA-Z0-9_]+)\s*=\s*.*?SuratService::(formatNomorSurat|NoSuratKeteranganTasmaKknPpl)/);
    if (!match) {
        // Fallback: check if we have something like $sk->nomor_surat = $noSurat;
        match = content.match(/nomor_surat\s*=\s*\$([a-zA-Z0-9_]+)/);
    }
    
    if (match) {
        const varName = match[1];
        
        // Replace $Nomor->nomor = $no_surat; (with any whitespace)
        // With $Nomor->nomor = $varName;
        let originalContent = content;
        
        // Because the user typed it as `$no_surat`, let's just replace `$no_surat;` when assigned to $Nomor->nomor or $log->nomor
        content = content.replace(/\$Nomor->nomor\s*=\s*\$no_surat;/g, `$Nomor->nomor = $${varName};`);
        content = content.replace(/\$log->nomor\s*=\s*\$no_surat;/g, `$log->nomor = $${varName};`);
        
        if (originalContent !== content) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`Updated ${file} to use $${varName}`);
        }
    }
});
