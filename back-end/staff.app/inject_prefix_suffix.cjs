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
    const jsId = mappings[dir];
    const filePath = path.join(basePath, dir, 'index.vue');
    
    if (!fs.existsSync(filePath)) {
        console.log(`Skipping ${dir}, not found`);
        return;
    }
    
    let content = fs.readFileSync(filePath, 'utf8');

    // Add import { computed } if needed
    if (!content.includes('import { computed }')) {
        content = content.replace(/(import .* from "vue";)/, '$1\nimport { computed } from "vue";');
    }

    // Add listJenisSurat state and functions if not present
    if (!content.includes('const listJenisSurat')) {
        const scriptToAdd = `
const listJenisSurat = ref<any[]>([]);

async function getJenisSurat() {
  try {
    const response = await apiGet(\`/jenis-surat\`);
    if (response.success) {
      const data = response.data?.data || response.data;
      listJenisSurat.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
    console.log(error);
  }
}

function getRoman(num: number) {
  const roman: any = { 1: "I", 2: "II", 3: "III", 4: "IV", 5: "V", 6: "VI", 7: "VII", 8: "VIII", 9: "IX", 10: "X", 11: "XI", 12: "XII" };
  return roman[num] || "";
}

const formatParts = computed(() => {
  const getFormat = (id: number) => {
    const js = listJenisSurat.value.find((x: any) => Number(x.id) === id);
    if (!js) return "";
    let str = js.format_surat;
    
    const dateObj = form.tanggal ? new Date(form.tanggal) : new Date();
    const dd = String(dateObj.getDate()).padStart(2, "0");
    const romanBulan = getRoman(dateObj.getMonth() + 1);
    const yyyy = dateObj.getFullYear();
    
    let aliasProdi = "";
    if (typeof listProdi !== 'undefined' && listProdi.value) {
        const prodiItem = listProdi.value.find((p: any) => Number(p.id) === Number(form.prodi_id));
        aliasProdi = prodiItem ? prodiItem.alias : "";
    }
    
    str = str.replace(/{TGL}/g, dd)
             .replace(/{BULAN}/g, romanBulan)
             .replace(/{TAHUN}/g, String(yyyy))
             .replace(/{PRODI}/g, aliasProdi);
             
    return str;
  };

  const parseToParts = (str: string) => {
    if(!str) return { prefix: "SU-", suffix: "" };
    const splitted = str.split("{NO}");
    return {
      prefix: splitted[0] || "",
      suffix: splitted[1] || ""
    };
  };

  return parseToParts(getFormat(${jsId}));
});
`;
        // insert before onMounted
        content = content.replace(/(onMounted\(\(\) => \{)/, scriptToAdd + '\n$1');
    }

    // Add getJenisSurat() inside onMounted
    if (!content.includes('getJenisSurat();')) {
        content = content.replace(/(onMounted\(\(\) => \{)/, '$1\n  getJenisSurat();');
    }

    // Replace <input type="text" v-model="form.no_surat"... /> with input-group
    // We might have already added an input for no_surat. Let's find it.
    const inputRegex = /<input\s+v-else\s+type="text"\s+v-model="form\.no_surat"[\s\S]*?<\/div>/;
    const inputRegexFallback = /<input\s+type="text"\s+v-model="form\.no_surat"[\s\S]*?<\/div>/;
    
    const inputGroupReplacement = `
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.prefix">{{ formatParts.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_surat"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.no_surat }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.suffix">{{ formatParts.suffix }}</span>
                  <div v-if="errors?.no_surat" class="invalid-feedback">
                    {{ errors.no_surat[0] }}
                  </div>
                </div>`;

    if (content.match(inputRegex)) {
        content = content.replace(inputRegex, inputGroupReplacement);
    } else if (content.match(inputRegexFallback)) {
        content = content.replace(inputRegexFallback, inputGroupReplacement);
    } else {
        console.log(`Input not found in ${dir}`);
    }

    fs.writeFileSync(filePath, content, 'utf8');
    console.log(`Updated ${dir}`);
});
