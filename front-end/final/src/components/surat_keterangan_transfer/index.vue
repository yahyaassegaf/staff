<script lang="ts" setup>
import { reactive, ref, watch, onMounted, nextTick } from "vue";
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
  th_akademik_id: null as null | number,
  nama: "",
  tanggal_lahir: "",
  nim: "",
  jurusan_prodi: "",
  semester: "",
  tahun_akademik: "",
  tanggal: "",
  jenis_kelamin: "",
  tempat_lahir: "",
  alamat: "",
  universitas_tujuan: "",
  fakultas: "",
  petanda_tangan: 'tidak',
};

const disableListMhsWatcher = ref(false);



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

  form.nama = val.nama;
  form.nim = val.nim;

  if (val.alias_prodi) {
    form.jurusan_prodi = val.alias_prodi;
  } else {
    form.jurusan_prodi = val.prodi_mhs;
  }

  if (val.jenis_kelamin) {
    form.jenis_kelamin = val.jenis_kelamin;
  }

  // Auto-fill tempat_lahir, alamat, and tanggal_lahir if returned from API
  form.tempat_lahir = val.tempat_lahir || val.tmp_lahir || "";
  form.alamat = val.alamat || val.alamat_rumah || val.jalan || "";
  if (val.tanggal_lahir) {
    form.tanggal_lahir = val.tanggal_lahir.slice(0, 10);
  }

  isLoadingData.value = false;
});

const listProdi = ref<any[]>([]);
const listThAkademik = ref<any[]>([]);
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

import { computed } from "vue";

function getRoman(num: number) {
  const roman: any = { 1: "I", 2: "II", 3: "III", 4: "IV", 5: "V", 6: "VI", 7: "VII", 8: "VIII", 9: "IX", 10: "X", 11: "XI", 12: "XII" };
  return roman[num] || "";
}

const formatParts = computed(() => {
  const parts = {
    skm: { prefix: "SU-", suffix: "" },
  };

  const getFormat = (alias: string) => {
    const js = listJenisSurat.value.find((x: any) => x.alias === alias);
    if (!js) return "";
    let str = js.format_surat;
    
    const dateObj = form.tanggal ? new Date(form.tanggal) : new Date();
    const dd = String(dateObj.getDate()).padStart(2, "0");
    const romanBulan = getRoman(dateObj.getMonth() + 1);
    const yyyy = dateObj.getFullYear();
    
    const prodiItem = listProdi.value.find((p) => Number(p.id) === Number(form.prodi_id));
    const aliasProdi = prodiItem ? prodiItem.alias : "";
    
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

  parts.skm = parseToParts(getFormat("SKM"));

  return parts;
});

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

async function getThAkademik() {
  try {
    const response = await apiGet(`/get-th-akademik`);
    if (response.success) {
      const data = response.data?.data;
      listThAkademik.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
  }
}


function extractNo(fullStr: string) {
  if (!fullStr) return "";
  const firstPart = fullStr.split("/")[0];
  return firstPart.replace("SU-", "").trim();
}

onMounted(() => {
  getProdi();
  getThAkademik();
  getJenisSurat();
});

function customName(params: any) {
  return `${params.nama} - ${params.nim}`;
}

watch(
  () => props.modelValue,
  async (val) => {
    if (!props.isEdit) return;

    if (!val || !val.nim) {
      isLoadingData.value = true;
      return;
    }

    disableListMhsWatcher.value = true;
    isLoadingData.value = true;

    // Pastikan listThAkademik sudah terisi sebelum set form
    if (listThAkademik.value.length === 0) {
      await getThAkademik();
    }

    await new Promise((resolve) => setTimeout(resolve, 300));

    const restVal = val;
    Object.assign(form, restVal);

    form.id = val.id ?? "";
    form.petanda_tangan = val.petanda_tangan ?? 'tidak';
    form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");
    if (!form.no_surat && form.nomor) {
      const match = form.nomor.match(/SU-\s*(\d+)/i) || form.nomor.match(/^(\d+)/);
      form.no_surat = match ? match[1] : "";
    }
    form.prodi_id = val.prodi_id ?? 0;
    form.nama = val.nama || val.nama_mhs || "";
    form.tanggal_lahir = val.tanggal_lahir
      ? val.tanggal_lahir.slice(0, 10)
      : "";
    form.nim = val.nim ?? "";
    form.jurusan_prodi = val.jurusan_prodi ?? "";
    form.semester = val.semester ?? "";
    form.tahun_akademik = val.tahun_akademik ?? "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";
    form.jenis_kelamin = val.jenis_kelamin ?? "";
    form.tempat_lahir = val.tempat_lahir ?? "";
    form.alamat = val.alamat ?? "";
    form.universitas_tujuan = val.universitas_tujuan ?? "";

    if (form.nim) {
      listMhs.value = {
        nim: form.nim,
        nama: form.nama,
        id: form.nim,
      };
      options.value = [listMhs.value];
    }

    // Set th_akademik_id jika ada
    form.th_akademik_id = val.th_akademik_id
      ? Number(val.th_akademik_id)
      : null;

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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Keterangan Transfer
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
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
                  :class="{ 'border-danger': errors?.nim || errors?.nama }"
                ></Multiselect>
                <div v-if="errors?.nim" class="text-danger small">
                  {{ errors.nim[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Mahasiswa</div>

              <div class="col-xl-6">
                <label class="form-label">Nama Mahasiswa :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama }"
                  readonly
                  placeholder="Isikan Nama Mahasiswa"
                />
                <div v-if="errors?.nama" class="invalid-feedback">
                  {{ errors.nama[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">NIM :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nim"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nim }"
                  readonly
                  placeholder="Isikan NIM"
                />
                <div v-if="errors?.nim" class="invalid-feedback">
                  {{ errors.nim[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label class="form-label">Tanggal Lahir :</label>
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

              <div class="col-xl-6">
                <label class="form-label">Jurusan / Program Studi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.jurusan_prodi"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.jurusan_prodi }"
                  readonly
                  placeholder="Isikan Jurusan / Program Studi"
                />
                <div v-if="errors?.jurusan_prodi" class="invalid-feedback">
                  {{ errors.jurusan_prodi[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tempat Lahir :</label>
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
                <label class="form-label">Alamat :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <textarea
                  v-else
                  v-model="form.alamat"
                  class="form-control"
                  rows="2"
                  :class="{ 'is-invalid': errors?.alamat }"
                  placeholder="Isikan Alamat Lengkap"
                ></textarea>
                <div v-if="errors?.alamat" class="invalid-feedback">
                  {{ errors.alamat[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Surat</div>

              <div class="col-xl-6">
                <label class="form-label">Nomor Surat (Hanya Angka) :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <div class="input-group" v-else>
                  <span class="input-group-text" v-if="formatParts.skm.prefix">{{ formatParts.skm.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_surat"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat || errors?.no_surat }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.skm.suffix">{{ formatParts.skm.suffix }}</span>
                  <div v-if="errors?.nomor_surat || errors?.no_surat" class="invalid-feedback">
                    {{ errors?.nomor_surat ? errors.nomor_surat[0] : errors?.no_surat[0] }}
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Universitas Tujuan :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.universitas_tujuan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.universitas_tujuan }"
                  placeholder="Isikan Nama Universitas Tujuan"
                />
                <div v-if="errors?.universitas_tujuan" class="invalid-feedback">
                  {{ errors.universitas_tujuan[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tahun Akademik :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tahun_akademik"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tahun_akademik }"
                  placeholder="Contoh: 2023/2024"
                />
                <div v-if="errors?.tahun_akademik" class="invalid-feedback">
                  {{ errors.tahun_akademik[0] }}
                </div>
              </div>

              <div class="col-xl-6">
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
