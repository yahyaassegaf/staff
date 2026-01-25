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
  nomor: "",
  prodi_id: 0,
  tanda_tangan_id: null as null | number,
  nama_penandatangan: "",
  niy: "",
  jabatan: "",
  nama_mhs: "",
  nim: "",
  prodi: "",
  fakultas: "",
  tanggal: "",
  jenis_kelamin: "",
};

const disableListMhsWatcher = ref(false);

const readonlyField = ref({
  nama_mhs: false,
  prodi: false,
  fakultas: false,
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
  form.nim = val.nim;

  if (val.alias_prodi) {
    form.prodi = val.alias_prodi;
    readonlyField.value.prodi = true;
  } else {
    form.prodi = val.prodi_mhs;
    readonlyField.value.prodi = false;
  }

  if (val.nama_fakultas) {
    form.fakultas = val.nama_fakultas;
    readonlyField.value.fakultas = true;
  }

  if (val.jenis_kelamin) {
    form.jenis_kelamin = val.jenis_kelamin;
  }
  isLoadingData.value = false;
});

const listProdi = ref<any[]>([]);
const listTandaTangan = ref<any[]>([]);

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

    if (!val || !val.nim) {
      isLoadingData.value = true;
      return;
    }

    disableListMhsWatcher.value = true;
    isLoadingData.value = true;

    // Pastikan listTandaTangan sudah terisi sebelum set form
    if (listTandaTangan.value.length === 0) {
      await getTandaTangan();
    }

    await new Promise((resolve) => setTimeout(resolve, 500));

    // Simpan tanda_tangan_id sebelum Object.assign
    const savedTandaTanganId = val.tanda_tangan_id
      ? Number(val.tanda_tangan_id)
      : null;

    Object.assign(form, val);

    form.id = val.id ?? "";
    form.nomor = val.nomor ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.tanda_tangan_id = savedTandaTanganId;
    form.nama_penandatangan = val.nama_penandatangan ?? "";
    form.niy = val.niy ?? "";
    form.jabatan = val.jabatan ?? "";
    form.nama_mhs = val.nama_mhs || val.nama_mahasiswa || "";
    form.nim = val.nim ?? "";
    form.prodi = val.prodi ?? "";
    form.fakultas = val.fakultas ?? "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";
    form.jenis_kelamin = val.jenis_kelamin ?? "";

    if (form.nim) {
      listMhs.value = {
        nim: form.nim,
        nama: form.nama_mhs,
        id: form.nim,
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
    console.log(error);
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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Pernyataan Melakukan
              Verifikasi Nilai Mahasiswa
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
                  :class="{ 'border-danger': errors?.nim || errors?.nama_mhs }"
                ></Multiselect>
                <div v-if="errors?.nim" class="text-danger small">
                  {{ errors.nim[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Penandatangan</div>

              <div class="col-xl-4">
                <label class="form-label">Nama yang bertanda tangan :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  v-model="form.tanda_tangan_id"
                  class="form-select"
                  :class="{ 'is-invalid': errors?.tanda_tangan_id }"
                >
                  <option :value="null">-- Pilih Penandatangan --</option>
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

              <div class="col-xl-4">
                <label class="form-label">NIY :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.niy"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.niy }"
                  placeholder="Isikan NIY"
                />
                <div v-if="errors?.niy" class="invalid-feedback">
                  {{ errors.niy[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Jabatan :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.jabatan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.jabatan }"
                  placeholder="Isikan Jabatan"
                />
                <div v-if="errors?.jabatan" class="invalid-feedback">
                  {{ errors.jabatan[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Data Mahasiswa</div>

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
                <label class="form-label">Program Studi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.prodi"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.prodi }"
                  :readonly="readonlyField.prodi"
                  placeholder="Isikan Program Studi"
                />
                <div v-if="errors?.prodi" class="invalid-feedback">
                  {{ errors.prodi[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Fakultas :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.fakultas"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.fakultas }"
                  :readonly="readonlyField.fakultas"
                  placeholder="Isikan Fakultas"
                />
                <div v-if="errors?.fakultas" class="invalid-feedback">
                  {{ errors.fakultas[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tanggal :</label>
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
            </div>
          </div>
          <div class="card-footer text-end">
            <button class="btn btn-primary btn-wave shadow-sm">
              {{ isEdit ? "Update Data" : "Simpan Data" }}
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
