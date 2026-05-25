const fs = require('fs');
const path = require('path');

const mappings = {
    'surat_izin_penelitian': 10,
    'surat_keterangan_administrasi_keuangan': 1,
    'surat_keterangan_aktif_mahasiswa': 8,
    'surat_keterangan': 11,
    'surat_keterangan_kkn': 3,
    'surat_keterangan_lulus_mata_kuliah': 2,
    'surat_keterangan_ppl': 3,
    'surat_keterangan_qismul_aman': 5,
    'surat_keterangan_tasma_kkn_ppl': 3,
    'surat_keterangan_ujian_komprehensif': 4,
    'surat_pernyataan_verifikasi_nilai': 7,
    'surat_tugas': 12,
    'surat_keterangan_daftar_s2': 15,
    'surat_keterangan_spm': 14,
    'surat_keterangan_transfer': 6
};

const basePath = 'c:/laragon/www/staff/front-end/final/src/components';

Object.keys(mappings).forEach(dir => {
    const filePath = path.join(basePath, dir, 'index.vue');
    
    if (!fs.existsSync(filePath)) {
        return;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');

    // Add extractNo function
    if (!content.includes('function extractNo')) {
        const extractFunc = `
function extractNo(fullStr: string) {
  if (!fullStr) return "";
  const firstPart = fullStr.split("/")[0];
  return firstPart.replace("SU-", "").trim();
}
`;
        content = content.replace(/(onMounted\(\(\) => \{)/, extractFunc + '\n$1');
    }

    // Replace form.no_surat assignment in watch block to use extractNo
    const watchAssignment = /form\.no_surat\s*=\s*(.*?);/g;
    
    let match;
    let newContent = content;
    
    // We want to replace lines like `form.no_surat = val.no_surat ?? val.nomor_surat ?? "";`
    // with `form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");`
    
    // But we need to make sure we only replace it if it's not already using extractNo
    if (content.includes('form.no_surat = val.')) {
        newContent = content.replace(/form\.no_surat\s*=\s*val\.no_surat\s*\?\?\s*val\.nomor_surat\s*\?\?\s*"";/, 'form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");');
    }

    fs.writeFileSync(filePath, newContent, 'utf8');
    console.log(`Updated extractNo for ${dir}`);
});
