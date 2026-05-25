const fs = require('fs');
const path = require('path');

const viewsDir = 'c:/laragon/www/staff/front-end/final/src/view';
const folders = fs.readdirSync(viewsDir);

for (const folder of folders) {
    if (folder.startsWith('surat_') || folder === 'sk_6') {
        const editFile = path.join(viewsDir, folder, 'edit', 'index.vue');
        if (fs.existsSync(editFile)) {
            let content = fs.readFileSync(editFile, 'utf8');
            
            // Inject no_surat and tanda_tangan_id after id: data.id,
            if (!content.includes('no_surat: data.no_surat')) {
                content = content.replace(/id:\s*data\.id,/, 'id: data.id,\n            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",\n            tanda_tangan_id: data.tanda_tangan_id ? Number(data.tanda_tangan_id) : null,');
                fs.writeFileSync(editFile, content, 'utf8');
                console.log('Injected safe in ' + editFile);
            }
        }
    }
}
