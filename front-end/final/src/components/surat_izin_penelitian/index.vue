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
  nama: "",
  nim: "",
  prodi_mhs: "",
  kepada: "",
  semester: "",
  dari_tanggal: "",
  tanggal: "",
  jenis_kelamin: "",
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
    form.prodi_mhs = val.alias_prodi;
  } else if (val.prodi_mhs) {
    form.prodi_mhs = val.prodi_mhs;
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
    console.log(error);
  }
}

onMounted(() => {
  getProdi();
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

    await new Promise((resolve) => setTimeout(resolve, 300));

    Object.assign(form, val);

    form.id = val.id ?? "";
    form.nomor = val.nomor ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.nama = val.nama || val.nama_mhs || val.nama_lengkap || "";
    form.nim = val.nim ?? "";
    form.prodi_mhs = val.prodi_mhs ?? "";
    form.kepada = val.kepada ?? "";
    form.semester = val.semester ?? "";
    form.dari_tanggal = val.dari_tanggal ? val.dari_tanggal.slice(0, 10) : "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";
    form.jenis_kelamin = val.jenis_kelamin ?? "";

    if (form.nim) {
      listMhs.value = {
        nim: form.nim,
        nama: form.nama,
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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Izin Penelitian
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-6">
                <label class="form-label">Program Studi Unit:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
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
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
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
                <div v-else-if="errors?.nama" class="text-danger small">
                  {{ errors.nama[0] }}
                </div>
              </div>
              <hr />
              <div class="card-title mb-0">Informasi Mahasiswa</div>

              <div class="col-xl-4">
                <label class="form-label">Nama Mahasiswa :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama }"
                  placeholder="Isikan Nama Mahasiswa"
                />
                <div v-if="errors?.nama" class="invalid-feedback">
                  {{ errors.nama[0] }}
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
                <label class="form-label">Program Studi Mahasiswa :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.prodi_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.prodi_mhs }"
                  placeholder="Isikan Program Studi"
                />
                <div v-if="errors?.prodi_mhs" class="invalid-feedback">
                  {{ errors.prodi_mhs[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Kepada (Tujuan Surat) :</label>
                <div
                  v-if="isLoadingData"
                  class="skeleton-input"
                  style="height: 74px"
                ></div>
                <textarea
                  v-else
                  v-model="form.kepada"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kepada }"
                  rows="2"
                  placeholder="Contoh: Pengasuh Pondok Pesantren Nurul Hikmah, Bogor"
                ></textarea>
                <div v-if="errors?.kepada" class="invalid-feedback">
                  {{ errors.kepada[0] }}
                </div>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Surat</div>

              <div class="col-xl-4">
                <label class="form-label">Semester :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.semester"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.semester }"
                  placeholder="Contoh: VI (Enam)"
                />
                <div v-if="errors?.semester" class="invalid-feedback">
                  {{ errors.semester[0] }}
                </div>
              </div>

              <div class="col-xl-4">
                <label class="form-label"
                  >Izin Penelitian Mulai Tanggal :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.dari_tanggal"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.dari_tanggal }"
                />
                <div v-if="errors?.dari_tanggal" class="invalid-feedback">
                  {{ errors.dari_tanggal[0] }}
                </div>
              </div>

              <div class="col-xl-4">
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

              <!-- <div class="col-xl-4">
                <label class="form-label">Jenis Kelamin :</label>
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.jenis_kelamin }"
                  v-model="form.jenis_kelamin"
                >
                  <option value="">Pilih Jenis Kelamin</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
                <div v-if="errors?.jenis_kelamin" class="invalid-feedback">
                  {{ errors.jenis_kelamin[0] }}
                </div>
              </div> -->
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
