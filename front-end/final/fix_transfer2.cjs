const fs = require('fs');
const file = 'c:/laragon/www/staff/front-end/final/src/view/surat_keterangan_transfer/edit/index.vue';
let content = fs.readFileSync(file, 'utf8');

// 1. Add fields to `ref` block
const refRegex = /tanggal_lahir:\s*"",\s*nim:\s*"",/;
content = content.replace(
    refRegex,
    'tanggal_lahir: "",\n      tempat_lahir: "",\n      alamat: "",\n      universitas_tujuan: "",\n      nim: "",'
);

// 2. Add fields to `suratData.value = { ... }` block
const mappingRegex = /tanggal_lahir:\s*data\.tanggal_lahir\s*\?\s*data\.tanggal_lahir\.slice\(0,\s*10\)\s*:\s*"",\s*nim:\s*data\.nim\s*\|\|\s*"",/;
content = content.replace(
    mappingRegex,
    'tanggal_lahir: data.tanggal_lahir\n              ? data.tanggal_lahir.slice(0, 10)\n              : "",\n            tempat_lahir: data.tempat_lahir || "",\n            alamat: data.alamat || "",\n            universitas_tujuan: data.universitas_tujuan || "",\n            nim: data.nim || "",'
);

fs.writeFileSync(file, content, 'utf8');
console.log('Fixed Transfer edit view mapping');
