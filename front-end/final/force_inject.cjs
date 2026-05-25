const fs = require('fs');
const path = require('path');

const viewsDir = 'c:/laragon/www/staff/front-end/final/src/view';
const folders = fs.readdirSync(viewsDir);

for (const folder of folders) {
    if (folder.startsWith('surat_') || folder === 'sk_6' || folder === 'hasil_rapat') {
        const editFile = path.join(viewsDir, folder, 'edit', 'index.vue');
        if (fs.existsSync(editFile)) {
            let content = fs.readFileSync(editFile, 'utf8');
            let modified = false;

            // 1. Force no_surat
            const noSuratRegex = /no_surat:\s*[^,\n]+,?/g;
            if (noSuratRegex.test(content)) {
                content = content.replace(noSuratRegex, 'no_surat: data.no_surat || data.nomor_surat || data.nomor || "",');
                modified = true;
            } else if (content.includes('id: data.id,')) {
                // If it doesn't even exist, inject it
                content = content.replace(/id:\s*data\.id,/, 'id: data.id,\n            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",');
                modified = true;
            }

            // 2. Force tanggal_lahir slice
            // Find `tanggal_lahir: data.tanggal_lahir || ""` or similar, but avoid matching the slice if it's already there
            const tlRegex = /tanggal_lahir:\s*data\.tanggal_lahir(\s*\|\|\s*"")?,/g;
            if (tlRegex.test(content)) {
                content = content.replace(tlRegex, 'tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.slice(0, 10) : "",');
                modified = true;
            }

            // 3. Force tanggal slice
            const tRegex = /tanggal:\s*data\.tanggal(\s*\|\|\s*"")?,/g;
            if (tRegex.test(content)) {
                content = content.replace(tRegex, 'tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",');
                modified = true;
            }

            if (modified) {
                fs.writeFileSync(editFile, content, 'utf8');
                console.log('Force updated ' + editFile);
            }
        }
    }
}
