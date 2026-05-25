const fs = require('fs');
const path = require('path');

const controllersDir = 'c:/laragon/www/staff/back-end/staff.app/app/Http/Controllers/Api';

const files = fs.readdirSync(controllersDir);

files.forEach(file => {
    if (!file.endsWith('Controller.php')) return;
    
    let filePath = path.join(controllersDir, file);
    let content = fs.readFileSync(filePath, 'utf8');

    let match = content.match(/\$([a-zA-Z0-9_]+)\s*=\s*.*?SuratService::(formatNomorSurat|NoSuratKeteranganTasmaKknPpl)/);
    if (!match) {
        match = content.match(/nomor_surat\s*=\s*\$([a-zA-Z0-9_]+)/);
    }
    
    if (match) {
        const varName = match[1];
        
        let originalContent = content;
        
        // Revert my bad changes
        content = content.replace(new RegExp(`\\$Nomor->nomor\\s*=\\s*\\$${varName};`, 'g'), `$Nomor->nomor = $no_surat;`);
        content = content.replace(new RegExp(`\\$log->nomor\\s*=\\s*\\$${varName};`, 'g'), `$log->nomor = $no_surat;`);
        
        if (originalContent !== content) {
            fs.writeFileSync(filePath, content, 'utf8');
            console.log(`Reverted ${file}`);
        }
    }
});
