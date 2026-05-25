const fs = require('fs');
const path = require('path');

const dirs = [
    'surat_izin_penelitian',
    'surat_keterangan_administrasi_keuangan',
    'surat_keterangan_aktif_mahasiswa',
    'surat_keterangan',
    'surat_keterangan_kkn',
    'surat_keterangan_lulus_mata_kuliah',
    'surat_keterangan_ppl',
    'surat_keterangan_qismul_aman',
    'surat_keterangan_tasma_kkn_ppl',
    'surat_keterangan_ujian_komprehensif',
    'surat_pernyataan_verifikasi_nilai',
    'surat_tugas'
];

const basePath = 'c:/laragon/www/staff/front-end/final/src/components';

dirs.forEach(dir => {
    const filePath = path.join(basePath, dir, 'index.vue');
    if (!fs.existsSync(filePath)) {
        console.log(`Skipping ${dir}, not found`);
        return;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');

    // 1. Add no_surat: "", to defaultForm if not exists
    if (!content.includes('no_surat:')) {
        content = content.replace(/(const defaultForm = \{\s*[\s\S]*?)(id: "",)/, '$1$2\n  no_surat: "",');
    }

    // 2. Add form.no_surat = val.no_surat to watch
    if (!content.includes('form.no_surat = val.no_surat')) {
        content = content.replace(/(form\.id = val\.id \?\? "";)/, '$1\n    form.no_surat = val.no_surat ?? val.nomor_surat ?? "";'); // sometimes it's nomor_surat from DB but we save as no_surat
    }

    // 3. Insert the template HTML inside card-body > row gy-3
    if (!content.includes('v-model="form.no_surat"')) {
        const inputHtml = `
              <div class="col-xl-12">
                <label class="form-label">Nomor Surat:</label>
                <input
                  type="text"
                  v-model="form.no_surat"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.no_surat }"
                  placeholder="Isikan Nomor Surat (cth: 001)"
                />
                <div v-if="errors?.no_surat" class="invalid-feedback">
                  {{ errors.no_surat[0] }}
                </div>
              </div>`;
        
        // Find <div class="row gy-3">
        content = content.replace(/(<div class="row gy-3">)/, '$1' + inputHtml);
    }

    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Updated ${dir}`);
});
