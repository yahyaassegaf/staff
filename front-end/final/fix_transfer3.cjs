const fs = require('fs');
const file = 'c:/laragon/www/staff/front-end/final/src/view/surat_keterangan_transfer/edit/index.vue';
let content = fs.readFileSync(file, 'utf8');

// 1. Add fields to `ref` block
content = content.replace(
    'nomor: "",',
    'no_surat: "",\n      nomor: "",'
);

// 2. Add fields to `suratData.value = { ... }` block
content = content.replace(
    'id: data.id,',
    'id: data.id,\n            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",'
);

fs.writeFileSync(file, content, 'utf8');
console.log('Fixed Transfer edit view mapping for no_surat');
