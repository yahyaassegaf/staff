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
});

const defaultForm = {
  id: "",
  no_surat: "",
  tanda_tangan_id: null as null | number,
  koordinator_kompre: null,
  prodi_id: 0,
  nama_lengkap: null,
  nama_mhs: "",
  tanggal_lahir: "",
  tempat_lahir: "",
  nim: "",
  prodi_mhs: "",
  alamat_rumah: "",
  kelas_pondok: "",
  tanggal: "",
  ttd: "",
};

const readonlyField = ref({
  nama_mhs: false,
  tanggal_lahir: false,
  tempat_lahir: false,
  kelas_pondok: false,
  alamat_rumah: false,
  prodi_mhs: false,
  koordinator_kompre: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const disableListMhsWatcher = ref(false);
const isLoadingData = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, async (val) => {
  if (!val) return;

  // Skip jika sedang dalam proses load edit data
  if (disableListMhsWatcher.value) return;

  isLoadingData.value = true;
  await new Promise((resolve) => setTimeout(resolve, 500));

  form.nama_mhs = val.nama;
  form.nim = val.nim;
  form.tanggal_lahir = val.tanggal_lahir ? val.tanggal_lahir.slice(0, 10) : "";
  form.tempat_lahir = val.tempat_lahir;
  form.prodi_mhs = val.alias_prodi;
  form.alamat_rumah = val.alamat;
  isLoadingData.value = false;
});

const listProdi = ref<any[]>([]);
const listTandaTangan = ref<any[]>([]);

async function getProdi() {
  try {
    const response = await apiGet(`/get-prodi`);
    console.log(response);

    if (response.success) {
      const data = response.data?.data;

      // paksa jadi array
      listProdi.value = Array.isArray(data) ? data : [data];

      if (listProdi.value.length === 1) {
        form.prodi_id = Number(listProdi.value[0].id);
      }
    }
  } catch (error) {
    console.log(error);
  }
}

async function getTandaTangan() {
  try {
    const response = await apiGet(`/tanda-tangan`);
    if (response.success) {
      const data =
        response.data?.data?.data || response.data?.data || response.data;
      listTandaTangan.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
    console.log(error);
  }
}

onMounted(() => {
  getProdi();
  getTandaTangan();
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

    // Pastikan listTandaTangan sudah terisi sebelum set form
    if (listTandaTangan.value.length === 0) {
      await getTandaTangan();
    }

    // Simulasi loading untuk efek skeleton
    await new Promise((resolve) => setTimeout(resolve, 300));

    // Simpan tanda_tangan_id sebelum Object.assign
    const savedTandaTanganId = val.tanda_tangan_id
      ? Number(val.tanda_tangan_id)
      : null;

    // Copy semua data kecuali tanda_tangan_id
    const { tanda_tangan_id, ...restVal } = val;
    Object.assign(form, restVal);

    form.nama_mhs =
      val.nama_mhs || val.nama_lengkap || val.nama_mahasiswa || "";

    if (form.nim) {
      listMhs.value = {
        nim: form.nim,
        nama: form.nama_mhs,
        id: form.nim,
      };
      options.value = [listMhs.value];
    }

    // Set tanda_tangan_id dengan nilai Number yang sudah dikonversi
    form.tanda_tangan_id = savedTandaTanganId;

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
    console.log(error);
  } finally {
    loading.value = false;
  }
}, 300);

const emit = defineEmits(["submit"]);

function submitForm() {
  emit("submit", form);
}

// watch(listProdi, (val) => {
//   if (val.length === 1) {
//     form.prodi_id = val[0].id;
//   }
// });
</script>

<template>
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">
              {{ isEdit ? "Edit" : "Tambah" }} Fakultas
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Program Studi:</label
                >
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
              <div class="col-xl-12">
                <label for="input-tanda-tangan" class="form-label"
                  >Koordinator Komprehensif:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  v-model="form.tanda_tangan_id"
                  class="form-select"
                  :class="{ 'is-invalid': errors?.tanda_tangan_id }"
                  id="input-tanda-tangan"
                >
                  <option :value="null">-- Pilih Koordinator --</option>
                  <option
                    v-for="ttd in listTandaTangan"
                    :key="ttd.id"
                    :value="Number(ttd.id)"
                  >
                    {{ ttd.nama }}
                  </option>
                </select>
                <div v-if="errors?.tanda_tangan_id" class="invalid-feedback">
                  {{ errors.tanda_tangan_id[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-nama" class="form-label"
                  >Nim Mahasiswa :</label
                >
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <Multiselect
                  :options="options"
                  v-model="listMhs"
                  :internal-search="false"
                  label="nama"
                  @search-change="getMhs"
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
              <div class="col-xl-6">
                <label for="input-kode" class="form-label"
                  >Nama Mahasiswa :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_mhs }"
                  :readonly="readonlyField.nama_mhs"
                  id="input-kode"
                  placeholder="Isikan nama mahasiswa"
                />
                <div v-if="errors?.nama_mhs" class="invalid-feedback">
                  {{ errors.nama_mhs[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Tempat Lahir:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tempat_lahir }"
                  :readonly="readonlyField.tempat_lahir"
                  id="input-nama-kepala"
                  placeholder="Isikan tempat lahir"
                />
                <div v-if="errors?.tempat_lahir" class="invalid-feedback">
                  {{ errors.tempat_lahir[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Tanggal Lahir:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal_lahir"
                  :readonly="readonlyField.tanggal_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_lahir }"
                  id="input-nama-kepala"
                  placeholder="Isikan tanggal lahir"
                />
                <div v-if="errors?.tanggal_lahir" class="invalid-feedback">
                  {{ errors.tanggal_lahir[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Prodi Mahasiswa:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.prodi_mhs"
                  :readonly="readonlyField.prodi_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.prodi_mhs }"
                  id="input-nama-kepala"
                  placeholder="Isikan prodi mahasiswa"
                />
                <div v-if="errors?.prodi_mhs" class="invalid-feedback">
                  {{ errors.prodi_mhs[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Kelas Pondok:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.kelas_pondok"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kelas_pondok }"
                  id="input-nama-kepala"
                  placeholder="Isikan kelas pondok"
                />
                <div v-if="errors?.kelas_pondok" class="invalid-feedback">
                  {{ errors.kelas_pondok[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Tanggal Surat:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal }"
                  id="input-nama-kepala"
                  placeholder="Isikan tanggal"
                />
                <div v-if="errors?.tanggal" class="invalid-feedback">
                  {{ errors.tanggal[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >alamat:</label
                >
                <div
                  v-if="isLoadingData"
                  class="skeleton-input"
                  style="height: 62px"
                ></div>
                <textarea
                  v-else
                  v-model="form.alamat_rumah"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_rumah }"
                  :readonly="readonlyField.alamat_rumah"
                  id="input-nama-kepala"
                  placeholder="Isikan alamat"
                ></textarea>
                <div v-if="errors?.alamat_rumah" class="invalid-feedback">
                  {{ errors.alamat_rumah[0] }}
                </div>
              </div>
            </div>
            <!-- <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-nama" class="form-label">Nama Prodi :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <input
                  type="text"
                  v-model="form.nama"
                  class="form-control"
                  id="input-nama"
                  placeholder="Isikan nama prodi"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-kode" class="form-label">Kode :</label>
                <input
                  type="text"
                  v-model="form.kode"
                  class="form-control"
                  id="input-kode"
                  placeholder="Isikan kode"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-alias" class="form-label">Alias :</label>
                <input
                  type="text"
                  v-model="form.alias"
                  class="form-control"
                  id="input-alias"
                  maxlength="5"
                  placeholder="Isikan alias"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-jenjang" class="form-label">Jenjang :</label>
                <select
                  class="form-select"
                  v-model="form.jenjang"
                  id="input-jenjang"
                  aria-label="Pilih jenjang"
                >
                  <option value="S1">S1</option>
                  <option value="S2">S2</option>
                  <option value="S3">S3</option>
                </select>
              </div>
              <div class="col-xl-6">
                <label for="input-nidn-kepala" class="form-label"
                  >NIDN Kepala :</label
                >
                <input
                  type="text"
                  v-model="form.nidn_kepala"
                  class="form-control"
                  id="input-nidn-kepala"
                  maxlength="15"
                  placeholder="Isikan NIDN kepala prodi"
                />
              </div>
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Kepala Prodi :</label
                >
                <input
                  type="text"
                  v-model="form.nama_kepala"
                  class="form-control"
                  id="input-nama-kepala"
                  placeholder="Isikan nama kepala prodi"
                />
              </div>
            </div> -->
          </div>
          <div class="card-footer">
            <button class="btn btn-primary-light btn-wave ms-auto float-end">
              {{ isEdit ? "Update" : "Simpan" }}
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

<style scoped></style>
