<script lang="ts" setup>
import { reactive, ref, watch, onMounted } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import { apiGet } from "../../services/api/request";
import { debounce } from "vuetify/lib/util/helpers.mjs";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
});

const defaultForm = {
  id: "",
  nomor: "",
  prodi_id: 0,
  dekan: "",
  nama: "",
  tanggal_lahir: "",
  nim: "",
  jurusan_prodi: "",
  semester: "",
  tahun_akademik: "",
  tanggal: "",
  jenis_kelamin: "",
};

const isFromSystem = ref(true);

const readonlyField = ref({
  nama: false,
  jurusan_prodi: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, (val) => {
  if (!val) return;

  if (isFromSystem.value) return;
  form.nama = val.nama;
  form.nim = val.nim;

  if (val.alias_prodi) {
    form.jurusan_prodi = val.alias_prodi;
    readonlyField.value.jurusan_prodi = true;
  } else {
    form.jurusan_prodi = val.prodi_mhs;
    readonlyField.value.jurusan_prodi = false;
  }

  if (val.jenis_kelamin) {
    form.jenis_kelamin = val.jenis_kelamin;
  }
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
  (val) => {
    if (!props.isEdit || !val) return;

    Object.assign(form, defaultForm);
    isFromSystem.value = true;

    form.id = val.id ?? "";
    form.nomor = val.nomor ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.dekan = val.dekan ?? "";
    form.nama = val.nama ?? "";
    form.tanggal_lahir = val.tanggal_lahir
      ? val.tanggal_lahir.slice(0, 10)
      : "";
    form.nim = val.nim ?? "";
    form.jurusan_prodi = val.jurusan_prodi ?? "";
    form.semester = val.semester ?? "";
    form.tahun_akademik = val.tahun_akademik ?? "";
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
  },
  { immediate: true }
);

const getMhs = debounce(async (params: string) => {
  const keyword = params.trim();
  try {
    loading.value = true;
    const response = await apiGet(`/get-mhs/${keyword}`);
    if (response.success) {
      options.value = response.data ? response.data : response.data.data;
    }
  } catch (error) {
    console.log(error);
  } finally {
    loading.value = false;
  }
}, 200);

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
                <select class="form-select" v-model="form.prodi_id">
                  <option
                    v-for="prodi in listProdi"
                    :key="prodi.id"
                    :value="Number(prodi.id)"
                  >
                    {{ prodi.nama }}
                  </option>
                </select>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Pencarian Mahasiswa (NIM):</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <Multiselect
                  :options="options"
                  v-model="listMhs"
                  :internal-search="false"
                  @search-change="getMhs"
                  @select="isFromSystem = false"
                  label="nama"
                  track-by="id"
                  :searchable="true"
                  :loading="loading"
                  :custom-label="customName"
                ></Multiselect>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Surat</div>

              <div class="col-xl-6">
                <label class="form-label">Dekan / Institusi :</label>
                <input
                  type="text"
                  v-model="form.dekan"
                  class="form-control"
                  placeholder="Isikan Nama Dekan / Institusi"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Semester :</label>
                <input
                  type="text"
                  v-model="form.semester"
                  class="form-control"
                  placeholder="Contoh: II (Dua)"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tahun Akademik :</label>
                <input
                  type="text"
                  v-model="form.tahun_akademik"
                  class="form-control"
                  placeholder="Contoh: 2023/2024"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tanggal Surat :</label>
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                />
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Mahasiswa</div>

              <div class="col-xl-4">
                <label class="form-label">Nama Mahasiswa :</label>
                <input
                  type="text"
                  v-model="form.nama"
                  class="form-control"
                  :readonly="readonlyField.nama"
                  placeholder="Isikan Nama Mahasiswa"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIM :</label>
                <input
                  type="text"
                  v-model="form.nim"
                  class="form-control"
                  placeholder="Isikan NIM"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Jenis Kelamin :</label>
                <select class="form-select" v-model="form.jenis_kelamin">
                  <option value="">Pilih Jenis Kelamin</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tanggal Lahir :</label>
                <input
                  type="date"
                  v-model="form.tanggal_lahir"
                  class="form-control"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Jurusan / Program Studi :</label>
                <input
                  type="text"
                  v-model="form.jurusan_prodi"
                  class="form-control"
                  :readonly="readonlyField.jurusan_prodi"
                  placeholder="Isikan Jurusan / Program Studi"
                />
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
