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
  nama_dosen: "",
  alamat_dosen: "",
  tugas_dosen: "",
  tugasnya: "",
  nama_mhs: "",
  nim_nik: "",
  fakultas_prodi: "",
  judul_skripsi: "",
  masa_penugasan: "",
  tanggal: "",
  jenis_kelamin: "",
};

const disableListMhsWatcher = ref(false);

const readonlyField = ref({
  nama_mhs: false,
  fakultas_prodi: false,
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

  if (val.alias_prodi) {
    form.fakultas_prodi = val.alias_prodi;
    readonlyField.value.fakultas_prodi = true;
  } else {
    form.fakultas_prodi = val.prodi_mhs;
    readonlyField.value.fakultas_prodi = false;
  }

  if (val.jenis_kelamin) {
    form.jenis_kelamin = val.jenis_kelamin;
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
    isLoadingData.value = true;

    await new Promise((resolve) => setTimeout(resolve, 500));
    Object.assign(form, val);

    form.id = val.id ?? "";
    form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");
    form.nomor = val.nomor ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.nama_dosen = val.nama_dosen ?? "";
    form.alamat_dosen = val.alamat_dosen ?? "";
    form.tugas_dosen = val.tugas_dosen ?? "";
    form.tugasnya = val.tugasnya ?? "";
    form.nama_mhs = val.nama_mhs || val.nama_lengkap || "";
    form.nim_nik = val.nim_nik ?? val.nim ?? "";
    form.fakultas_prodi = val.fakultas_prodi ?? "";
    form.judul_skripsi = val.judul_skripsi ?? "";
    form.masa_penugasan = val.masa_penugasan ?? "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";
    form.jenis_kelamin = val.jenis_kelamin ?? "";

    if (form.nim_nik) {
      listMhs.value = {
        nim: form.nim_nik,
        nama: form.nama_mhs,
        id: form.nim_nik,
      };
      options.value = [listMhs.value];
    }
    isLoadingData.value = false;

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
              <div class="card-title mb-0">Informasi Dosen</div>

              <div class="col-xl-4">
                <label class="form-label">Nama Dosen :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_dosen"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_dosen }"
                  placeholder="Isikan Nama Dosen"
                />
                <div v-if="errors?.nama_dosen" class="invalid-feedback">
                  {{ errors.nama_dosen[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Alamat Dosen :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.alamat_dosen"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_dosen }"
                  placeholder="Isikan Alamat Dosen"
                />
                <div v-if="errors?.alamat_dosen" class="invalid-feedback">
                  {{ errors.alamat_dosen[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tugas Dosen :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tugas_dosen"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tugas_dosen }"
                  placeholder="Isikan Tugas Dosen (Contoh: Dosen Pembimbing)"
                />
                <div v-if="errors?.tugas_dosen" class="invalid-feedback">
                  {{ errors.tugas_dosen[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Deskripsi Tugas (Tugasnya) :</label>
                <div
                  v-if="isLoadingData"
                  class="skeleton-input"
                  style="height: 62px"
                ></div>
                <textarea
                  v-else
                  v-model="form.tugasnya"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tugasnya }"
                  rows="3"
                  placeholder="Isikan Detail Tugas yang Dilakukan"
                ></textarea>
                <div v-if="errors?.tugasnya" class="invalid-feedback">
                  {{ errors.tugasnya[0] }}
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
                <label class="form-label">Fakultas / Prodi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.fakultas_prodi"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.fakultas_prodi }"
                  :readonly="readonlyField.fakultas_prodi"
                  placeholder="Isikan Fakultas atau Program Studi"
                />
                <div v-if="errors?.fakultas_prodi" class="invalid-feedback">
                  {{ errors.fakultas_prodi[0] }}
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
                  placeholder="Contoh: Semester Ganjil 2023/2024"
                />
                <div v-if="errors?.masa_penugasan" class="invalid-feedback">
                  {{ errors.masa_penugasan[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Tanggal Surat :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal }"
                />
                <div v-if="errors?.tanggal" class="invalid-feedback">
                  {{ errors.tanggal[0] }}
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
