const fs = require('fs');
const path = require('path');

const viewsDir = 'c:/laragon/www/staff/front-end/final/src/view';
const folders = fs.readdirSync(viewsDir);

for (const folder of folders) {
    if (folder.startsWith('surat_') || folder === 'sk_6') {
        const editFile = path.join(viewsDir, folder, 'edit', 'index.vue');
        if (fs.existsSync(editFile)) {
            let content = fs.readFileSync(editFile, 'utf8');
            
            // Match suratData.value = { ... };
            // It starts with suratData.value = {
            // and ends with }; right before } of the if block.
            
            const regex = /suratData\.value\s*=\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\};/s;
            
            if (regex.test(content)) {
                content = content.replace(regex, `suratData.value = Object.assign({}, data, {
            no_surat: data.no_surat || data.nomor_surat || data.nomor || ""
          });`);
                fs.writeFileSync(editFile, content, 'utf8');
                console.log('Fixed ' + editFile);
            } else {
                console.log('Did not match in ' + editFile);
            }
        }
    }
}
