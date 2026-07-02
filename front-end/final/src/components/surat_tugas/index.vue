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
  nomor: "",
  prodi_id: 0,
  pebimbing1: "",
  alamat_pebimbing1: "",
  tugas_pebimbing1: "",
  pebimbing2: "",
  alamat_pebimbing2: "",
  tugas_pebimbing2: "",
  nama_mhs: "",
  nim_nik: "",
  judul_skripsi: "",
  masa_penugasan: "",
  petanda_tangan: 'tidak',
};

const disableListMhsWatcher = ref(false);

const readonlyField = ref({
  nama_mhs: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const isLoadingData = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, async (val) => {
  if (!val) return;

  if (disableListMhsWatcher.value) return;

  isLoadingData.value = true;
  await new Promise((resolve) => setTimeout(resolve, 500));

  form.nama_mhs = val.nama;
  form.nim_nik = val.nim;

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
    
    const dateObj = new Date(); // removed form.tanggal
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

  return parseToParts(getFormat(12));
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

    if (!val || !(val.nim_nik || val.nim)) {
      isLoadingData.value = true;
      return;
    }

    disableListMhsWatcher.value = true;
    disableListDosenWatcher.value = true;
    isLoadingData.value = true;

    await new Promise((resolve) => setTimeout(resolve, 500));
    Object.assign(form, val);

    form.id = val.id ?? "";
    form.petanda_tangan = val.petanda_tangan ?? 'tidak';
    form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");
    form.nomor = val.nomor ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    
    form.pebimbing1 = val.pebimbing1 ?? "";
    form.alamat_pebimbing1 = val.alamat_pebimbing1 ?? "";
    form.tugas_pebimbing1 = val.tugas_pebimbing1 ?? "";
    
    form.pebimbing2 = val.pebimbing2 ?? "";
    form.alamat_pebimbing2 = val.alamat_pebimbing2 ?? "";
    form.tugas_pebimbing2 = val.tugas_pebimbing2 ?? "";

    form.nama_mhs = val.nama_mhs || val.nama_lengkap || "";
    form.nim_nik = val.nim_nik ?? val.nim ?? "";
    form.judul_skripsi = val.judul_skripsi ?? "";
    form.masa_penugasan = val.masa_penugasan ?? "";

    if (form.nim_nik) {
      listMhs.value = {
        nim: form.nim_nik,
        nama: form.nama_mhs,
        id: form.nim_nik,
      };
      options.value = [listMhs.value];
    }
    
    if (form.pebimbing1) {
      listDosen1.value = { nama: form.pebimbing1, alamat: form.alamat_pebimbing1 };
      optionsDosen1.value = [listDosen1.value];
    }
    
    if (form.pebimbing2) {
      listDosen2.value = { nama: form.pebimbing2, alamat: form.alamat_pebimbing2 };
      optionsDosen2.value = [listDosen2.value];
    }
    
    isLoadingData.value = false;

    await nextTick();
    disableListMhsWatcher.value = false;
    disableListDosenWatcher.value = false;
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

const listDosen1 = ref<any>(null);
const listDosen2 = ref<any>(null);
const disableListDosenWatcher = ref(false);

const optionsDosen1 = ref<any[]>([]);
const loadingDosen1 = ref(false);

const getDosen1 = debounce(async (params: string) => {
  const keyword = params.trim();
  if (!keyword && !props.isEdit) {
    optionsDosen1.value = [];
    return;
  }

  try {
    loadingDosen1.value = true;
    const response = await apiGet(`/get-dosen`, { search: keyword });
    if (response.success) {
      optionsDosen1.value = response.data?.data || [];
    }
  } catch (error) {
  } finally {
    loadingDosen1.value = false;
  }
}, 300);

const optionsDosen2 = ref<any[]>([]);
const loadingDosen2 = ref(false);

const getDosen2 = debounce(async (params: string) => {
  const keyword = params.trim();
  if (!keyword && !props.isEdit) {
    optionsDosen2.value = [];
    return;
  }

  try {
    loadingDosen2.value = true;
    const response = await apiGet(`/get-dosen`, { search: keyword });
    if (response.success) {
      optionsDosen2.value = response.data?.data || [];
    }
  } catch (error) {
  } finally {
    loadingDosen2.value = false;
  }
}, 300);

function customNameDosen(params: any) {
  return params.nidn ? `${params.nama} - ${params.nidn}` : params.nama;
}

watch(listDosen1, (val) => {
  if (disableListDosenWatcher.value) return;
  if (val) {
    form.pebimbing1 = val.nama;
    form.alamat_pebimbing1 = val.alamat || val.alamat_lengkap || val.alamat_rumah || val.alamat_tinggal || val.alamat_ktp || val.address || '';
  } else {
    form.pebimbing1 = '';
    form.alamat_pebimbing1 = '';
  }
});

watch(listDosen2, (val) => {
  if (disableListDosenWatcher.value) return;
  if (val) {
    form.pebimbing2 = val.nama;
    form.alamat_pebimbing2 = val.alamat || val.alamat_lengkap || val.alamat_rumah || val.alamat_tinggal || val.alamat_ktp || val.address || '';
  } else {
    form.pebimbing2 = '';
    form.alamat_pebimbing2 = '';
  }
});

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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Tugas
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
                  :class="{
                    'border-danger': errors?.nim_nik || errors?.nama_mhs,
                  }"
                ></Multiselect>
                <div v-if="errors?.nim_nik" class="text-danger small">
                  {{ errors.nim_nik[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Dosen (Pembimbing 1)</div>

              <div class="col-xl-3">
                <label class="form-label">Cari Dosen Pembimbing 1 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  :options="optionsDosen1"
                  v-model="listDosen1"
                  :internal-search="false"
                  @search-change="getDosen1"
                  label="nama"
                  track-by="nama"
                  :searchable="true"
                  :loading="loadingDosen1"
                  :custom-label="customNameDosen"
                  placeholder="Cari Dosen Pembimbing 1"
                ></Multiselect>
              </div>

              <div class="col-xl-3">
                <label class="form-label">Nama Pembimbing 1 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.pebimbing1"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.pebimbing1 }"
                  placeholder="Nama Pembimbing 1"
                  readonly
                />
                <div v-if="errors?.pebimbing1" class="invalid-feedback">
                  {{ errors.pebimbing1[0] }}
                </div>
              </div>

              <div class="col-xl-3">
                <label class="form-label">Alamat Pembimbing 1 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.alamat_pebimbing1"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_pebimbing1 }"
                  placeholder="Alamat Pembimbing 1"
                />
                <div v-if="errors?.alamat_pebimbing1" class="invalid-feedback">
                  {{ errors.alamat_pebimbing1[0] }}
                </div>
              </div>

              <div class="col-xl-3">
                <label class="form-label">Tugas Pembimbing 1 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tugas_pebimbing1"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tugas_pebimbing1 }"
                  placeholder="Tugas (misal: Pembimbing 1)"
                />
                <div v-if="errors?.tugas_pebimbing1" class="invalid-feedback">
                  {{ errors.tugas_pebimbing1[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Dosen (Pembimbing 2)</div>

              <div class="col-xl-3">
                <label class="form-label">Cari Dosen Pembimbing 2 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  :options="optionsDosen2"
                  v-model="listDosen2"
                  :internal-search="false"
                  @search-change="getDosen2"
                  label="nama"
                  track-by="nama"
                  :searchable="true"
                  :loading="loadingDosen2"
                  :custom-label="customNameDosen"
                  placeholder="Cari Dosen Pembimbing 2"
                ></Multiselect>
              </div>

              <div class="col-xl-3">
                <label class="form-label">Nama Pembimbing 2 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.pebimbing2"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.pebimbing2 }"
                  placeholder="Nama Pembimbing 2"
                  readonly
                />
                <div v-if="errors?.pebimbing2" class="invalid-feedback">
                  {{ errors.pebimbing2[0] }}
                </div>
              </div>

              <div class="col-xl-3">
                <label class="form-label">Alamat Pembimbing 2 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.alamat_pebimbing2"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_pebimbing2 }"
                  placeholder="Alamat Pembimbing 2"
                />
                <div v-if="errors?.alamat_pebimbing2" class="invalid-feedback">
                  {{ errors.alamat_pebimbing2[0] }}
                </div>
              </div>

              <div class="col-xl-3">
                <label class="form-label">Tugas Pembimbing 2 :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tugas_pebimbing2"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tugas_pebimbing2 }"
                  placeholder="Tugas (misal: Pembimbing 2)"
                />
                <div v-if="errors?.tugas_pebimbing2" class="invalid-feedback">
                  {{ errors.tugas_pebimbing2[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Mahasiswa & Penugasan</div>

              <div class="col-xl-6">
                <label class="form-label">Nama Mahasiswa :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_mhs }"
                  :readonly="readonlyField.nama_mhs"
                  placeholder="Isikan Nama Mahasiswa"
                />
                <div v-if="errors?.nama_mhs" class="invalid-feedback">
                  {{ errors.nama_mhs[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">NIM / NIK :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nim_nik"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nim_nik }"
                  placeholder="Isikan NIM atau NIK"
                />
                <div v-if="errors?.nim_nik" class="invalid-feedback">
                  {{ errors.nim_nik[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Masa Penugasan :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.masa_penugasan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.masa_penugasan }"
                />
                <div v-if="errors?.masa_penugasan" class="invalid-feedback">
                  {{ errors.masa_penugasan[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Judul Skripsi :</label>
                <div
                  v-if="isLoadingData"
                  class="skeleton-input"
                  style="height: 62px"
                ></div>
                <textarea
                  v-else
                  v-model="form.judul_skripsi"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.judul_skripsi }"
                  rows="3"
                  placeholder="Isikan Judul Skripsi Mahasiswa"
                ></textarea>
                <div v-if="errors?.judul_skripsi" class="invalid-feedback">
                  {{ errors.judul_skripsi[0] }}
                </div>
              </div>
              <div class="col-xl-12 mt-3">
                <label class="form-label fw-bold">Pakai Tanda Tangan & Stempel :</label>
                <div v-if="isLoadingData" class="skeleton-input" style="width: 150px;"></div>
                <div v-else class="d-flex align-items-center mt-2">
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" :name="'petanda_tangan_' + Date.now()" id="ttd_keduanya" value="ya" v-model="form.petanda_tangan">
                    <label class="form-check-label" for="ttd_keduanya">tandatangan+stempel</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" :name="'petanda_tangan_' + Date.now()" id="ttd_kosong" value="tidak" v-model="form.petanda_tangan">
                    <label class="form-check-label" for="ttd_kosong">kosong</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" :name="'petanda_tangan_' + Date.now()" id="ttd_stempel" value="stempel" v-model="form.petanda_tangan">
                    <label class="form-check-label" for="ttd_stempel">stempel saja</label>
                  </div>
                </div>
                <div v-if="errors?.petanda_tangan" class="invalid-feedback d-block">
                  {{ errors.petanda_tangan[0] }}
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
