const fs = require('fs');
const path = require('path');

const viewsDir = 'c:/laragon/www/staff/front-end/final/src/view';
const folders = fs.readdirSync(viewsDir);

for (const folder of folders) {
    if (folder.startsWith('surat_') || folder === 'sk_6') {
        const editFile = path.join(viewsDir, folder, 'edit', 'index.vue');
        if (fs.existsSync(editFile)) {
            let content = fs.readFileSync(editFile, 'utf8');

            // We want to find the ref block: const suratData = ref({ ... })
            // Inside that block, we might have no_surat: data.no_surat ...
            // We should replace it with no_surat: "",
            
            // Regex to find the ref block
            const refRegex = /const\s+suratData\s*=\s*ref\(\{([\s\S]*?)\}\);/g;
            content = content.replace(refRegex, (match, inner) => {
                let fixedInner = inner.replace(/no_surat:\s*data\.no_surat\s*\|\|[^,\n]+,/, 'no_surat: "",');
                return `const suratData = ref({${fixedInner}});`;
            });

            fs.writeFileSync(editFile, content, 'utf8');
            console.log('Fixed ref in ' + editFile);
        }
    }
}
