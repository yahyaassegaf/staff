const fs = require('fs');
const file = 'c:/laragon/www/staff/front-end/final/src/view/surat_keterangan_transfer/edit/index.vue';
let content = fs.readFileSync(file, 'utf8');

// 1. Add fields to `ref` block
content = content.replace(
    'tanggal_lahir: "",\n      nim: "",',
    'tanggal_lahir: "",\n      tempat_lahir: "",\n      alamat: "",\n      universitas_tujuan: "",\n      nim: "",'
);

// 2. Add fields to `suratData.value = { ... }` block
content = content.replace(
    '            tanggal_lahir: data.tanggal_lahir\n              ? data.tanggal_lahir.slice(0, 10)\n              : "",\n            nim: data.nim || "",',
    '            tanggal_lahir: data.tanggal_lahir\n              ? data.tanggal_lahir.slice(0, 10)\n              : "",\n            tempat_lahir: data.tempat_lahir || "",\n            alamat: data.alamat || "",\n            universitas_tujuan: data.universitas_tujuan || "",\n            nim: data.nim || "",'
);

fs.writeFileSync(file, content, 'utf8');
console.log('Fixed Transfer edit view mapping');
