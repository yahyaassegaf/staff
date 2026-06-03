<script lang="ts" setup>
import { reactive, ref, watch, onMounted, nextTick } from "vue";
import { computed } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import { apiGet } from "../../services/api/request";
import { debounce } from "vuetify/lib/util/helpers.mjs";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
  errors: {
    type: Object,
    default: () => ({}),
  },
  btnLoading: {
    type: Boolean,
    default: false,
  },
});

const defaultForm = {
  id: "",
  no_surat: "",
  nomor_surat: "",
  prodi_id: 0,
  nama_mhs: "",
  nim: "",
  nik: "",
  tempat_lahir: "",
  tanggal_lahir: "",
  prodi_mhs: "",
  semester: "",
  tahun_akademik: "",
  nama_ortu: "",
  nik_ortu: "",
  alamat_ortu: "",
  hp_ortu: "",
  tanggal: "",
};

const disableListMhsWatcher = ref(false);

const readonlyField = ref({
  tanggal_lahir: false,
  tempat_lahir: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const isLoadingData = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, async (val) => {
  if (!val) return;

  // Skip jika sedang dalam proses load edit data
  if (disableListMhsWatcher.value) return;

  isLoadingData.value = true;
  // Simulasikan loading agar user melihat proses pengisian data
  await new Promise((resolve) => setTimeout(resolve, 500));

  form.nama_mhs = val.nama;
  form.nim = val.nim;
  form.tanggal_lahir = val.tanggal_lahir ? val.tanggal_lahir.slice(0, 10) : "";
  form.tempat_lahir = val.tempat_lahir;

  if (val.alias_prodi) {
    form.prodi_mhs = val.alias_prodi;
  } else {
    form.prodi_mhs = val.prodi_mhs;
  }
  isLoadingData.value = false;
});

const listProdi = ref<any[]>([]);

async function getProdi() {
  try {
    const response = await apiGet(`/get-prodi`);
    if (response.success) {
      const data = response.data?.data;
      listProdi.value = Array.isArray(data) ? data : [data];
      if (listProdi.value.length === 1) {
        form.prodi_id = Number(listProdi.value[0].id);
      }
    }
  } catch (error) {
  }
}


const listJenisSurat = ref<any[]>([]);

async function getJenisSurat() {
  try {
    const response = await apiGet(`/jenis-surat`);
    if (response.success) {
      const data = response.data?.data || response.data;
      listJenisSurat.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
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

  return parseToParts(getFormat(8));
});


function extractNo(fullStr: string) {
  if (!fullStr) return "";
  const firstPart = fullStr.split("/")[0];
  return firstPart.replace("SU-", "").trim();
}

onMounted(() => {
  getJenisSurat();
  getProdi();
});

function customName(params: any) {
  return `${params.nama} - ${params.nim}`;
}

watch(
  () => props.modelValue,
  async (val) => {
    if (!props.isEdit) return;

    // Tampilkan skeleton loading saat menunggu data
    if (!val || !val.nim) {
      isLoadingData.value = true;
      return;
    }

    // Disable watcher listMhs agar tidak terpicu saat set listMhs.value
    disableListMhsWatcher.value = true;
    isLoadingData.value = true;

    // Simulasi loading untuk efek skeleton
    await new Promise((resolve) => setTimeout(resolve, 500));

    Object.assign(form, val);

    form.id = val.id ?? "";
    form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");
    form.nomor_surat = val.nomor_surat ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.nama_mhs = val.nama_mhs || val.nama_lengkap || "";
    form.nim = val.nim ?? "";
    form.nik = val.nik ?? "";
    form.tempat_lahir = val.tempat_lahir ?? "";
    form.tanggal_lahir = val.tanggal_lahir
      ? val.tanggal_lahir.slice(0, 10)
      : "";
    form.prodi_mhs = val.prodi_mhs ?? "";
    form.semester = val.semester ?? "";
    form.tahun_akademik = val.tahun_akademik ?? "";
    form.nama_ortu = val.nama_ortu ?? "";
    form.nik_ortu = val.nik_ortu ?? "";
    form.alamat_ortu = val.alamat_ortu ?? "";
    form.hp_ortu = val.hp_ortu ?? "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";

    if (form.nim) {
      listMhs.value = {
        nim: form.nim,
        nama: form.nama_mhs,
        id: form.nim,
      };
      options.value = [listMhs.value];
    }

    isLoadingData.value = false;

    // Reset flag setelah nextTick agar watcher listMhs aktif untuk user select
    await nextTick();
    disableListMhsWatcher.value = false;
  },
  { immediate: true }
);

const getMhs = debounce(async (params: string) => {
  const keyword = params.trim();
  if (!keyword && !props.isEdit) {
    options.value = [];
    return;
  }

  try {
    loading.value = true;
    const response = await apiGet(`/get-mhs`, { search: keyword });
    if (response.success) {
      const result = response.data;
      if (result && result.data && Array.isArray(result.data)) {
        options.value = result.data;
      } else if (Array.isArray(result)) {
        options.value = result;
      } else {
        options.value = [];
      }
    }
  } catch (error) {
  } finally {
    loading.value = false;
  }
}, 300);

const emit = defineEmits(["submit"]);

function submitForm() {
  emit("submit", form);
}
</script>

<template>
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">
              {{ isEdit ? "Edit" : "Tambah" }} Surat Keterangan Aktif Mahasiswa
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label class="form-label">Nomor Surat:</label>
                
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.prefix">{{ formatParts.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_surat"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat || errors?.no_surat }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.suffix">{{ formatParts.suffix }}</span>
                  <div v-if="errors?.nomor_surat || errors?.no_surat" class="invalid-feedback">
                    {{ errors?.nomor_surat ? errors.nomor_surat[0] : errors?.no_surat[0] }}
                  </div>
                </div>
              </div>
              <div class="col-xl-6">
                <label class="form-label">Program Studi Unit:</label>
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.prodi_id }"
                  v-model="form.prodi_id"
                >
                  <option
                    v-for="prodi in listProdi"
                    :key="prodi.id"
                    :value="Number(prodi.id)"
                  >
                    {{ prodi.nama }}
                  </option>
                </select>
                <div v-if="errors?.prodi_id" class="invalid-feedback">
                  {{ errors.prodi_id[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Pencarian Mahasiswa (NIM):</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <Multiselect
                  :options="options"
                  v-model="listMhs"
                  :internal-search="false"
                  @search-change="getMhs"
                  label="nama"
                  track-by="id"
                  :searchable="true"
                  :loading="loading"
                  :custom-label="customName"
                  :class="{ 'border-danger': errors?.nim || errors?.nama_mhs }"
                ></Multiselect>
                <div v-if="errors?.nim" class="text-danger small">
                  {{ errors.nim[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Nama Mahasiswa :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_mhs }"
                  readonly
                  placeholder="Isikan Nama Mahasiswa"
                />
                <div v-if="errors?.nama_mhs" class="invalid-feedback">
                  {{ errors.nama_mhs[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIM :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nim"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nim }"
                  placeholder="Isikan NIM"
                />
                <div v-if="errors?.nim" class="invalid-feedback">
                  {{ errors.nim[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIK Mahasiswa :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nik"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nik }"
                  placeholder="Isikan NIK"
                />
                <div v-if="errors?.nik" class="invalid-feedback">
                  {{ errors.nik[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tempat Lahir:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tempat_lahir }"
                  placeholder="Isikan Tempat Lahir"
                />
                <div v-if="errors?.tempat_lahir" class="invalid-feedback">
                  {{ errors.tempat_lahir[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tanggal Lahir:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_lahir }"
                />
                <div v-if="errors?.tanggal_lahir" class="invalid-feedback">
                  {{ errors.tanggal_lahir[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Program Studi Mahasiswa:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.prodi_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.prodi_mhs }"
                  readonly
                  placeholder="Isikan Program Studi"
                />
                <div v-if="errors?.prodi_mhs" class="invalid-feedback">
                  {{ errors.prodi_mhs[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Semester:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.semester"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.semester }"
                  placeholder="Isikan Semester (Contoh: V / Lima)"
                />
                <div v-if="errors?.semester" class="invalid-feedback">
                  {{ errors.semester[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tahun Akademik:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tahun_akademik"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tahun_akademik }"
                  placeholder="Misal: 2024/2025"
                />
                <div v-if="errors?.tahun_akademik" class="invalid-feedback">
                  {{ errors.tahun_akademik[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Data Orang Tua</div>

              <div class="col-xl-6">
                <label class="form-label">Nama Orang Tua:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_ortu"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_ortu }"
                  placeholder="Isikan Nama Orang Tua"
                />
                <div v-if="errors?.nama_ortu" class="invalid-feedback">
                  {{ errors.nama_ortu[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">NIK Orang Tua:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nik_ortu"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nik_ortu }"
                  placeholder="Isikan NIK Orang Tua"
                />
                <div v-if="errors?.nik_ortu" class="invalid-feedback">
                  {{ errors.nik_ortu[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Alamat Orang Tua:</label>
                <div
                  v-if="isLoadingData"
                  class="skeleton-input"
                  style="height: 74px"
                ></div>
                <textarea
                  v-else
                  v-model="form.alamat_ortu"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_ortu }"
                  rows="2"
                  placeholder="Isikan Alamat Lengkap Orang Tua"
                ></textarea>
                <div v-if="errors?.alamat_ortu" class="invalid-feedback">
                  {{ errors.alamat_ortu[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">No. HP / Kontak Ortu:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.hp_ortu"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.hp_ortu }"
                  placeholder="Isikan No. HP Orang Tua"
                />
                <div v-if="errors?.hp_ortu" class="invalid-feedback">
                  {{ errors.hp_ortu[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Tanggal Surat:</label>
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal }"
                />
                <div v-if="errors?.tanggal" class="invalid-feedback">
                  {{ errors.tanggal[0] }}
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary-light btn-wave ms-auto float-end" :disabled="btnLoading">
              <span v-if="btnLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ btnLoading ? (isEdit ? "Mengupdate..." : "Menyimpan...") : (isEdit ? "Update" : "Simpan") }}
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<style scoped>
.skeleton-input {
  height: 38px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 5px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}
</style>
