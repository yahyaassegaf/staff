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

const isFromSystem = ref(true);

const readonlyField = ref({
  nama_mhs: false,
  fakultas_prodi: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, (val) => {
  if (!val) return;

  if (isFromSystem.value) return;
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
    form.nama_dosen = val.nama_dosen ?? "";
    form.alamat_dosen = val.alamat_dosen ?? "";
    form.tugas_dosen = val.tugas_dosen ?? "";
    form.tugasnya = val.tugasnya ?? "";
    form.nama_mhs = val.nama_mhs ?? "";
    form.nim_nik = val.nim_nik ?? "";
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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Tugas
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
              <div class="card-title mb-0">Informasi Dosen</div>

              <div class="col-xl-4">
                <label class="form-label">Nama Dosen :</label>
                <input
                  type="text"
                  v-model="form.nama_dosen"
                  class="form-control"
                  placeholder="Isikan Nama Dosen"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Alamat Dosen :</label>
                <input
                  type="text"
                  v-model="form.alamat_dosen"
                  class="form-control"
                  placeholder="Isikan Alamat Dosen"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tugas Dosen :</label>
                <input
                  type="text"
                  v-model="form.tugas_dosen"
                  class="form-control"
                  placeholder="Isikan Tugas Dosen (Contoh: Dosen Pembimbing)"
                />
              </div>

              <div class="col-xl-12">
                <label class="form-label">Deskripsi Tugas (Tugasnya) :</label>
                <textarea
                  v-model="form.tugasnya"
                  class="form-control"
                  rows="3"
                  placeholder="Isikan Detail Tugas yang Dilakukan"
                ></textarea>
              </div>

              <hr />
              <div class="card-title mb-0">Informasi Mahasiswa & Penugasan</div>

              <div class="col-xl-4">
                <label class="form-label">Nama Mahasiswa :</label>
                <input
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :readonly="readonlyField.nama_mhs"
                  placeholder="Isikan Nama Mahasiswa"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIM / NIK :</label>
                <input
                  type="text"
                  v-model="form.nim_nik"
                  class="form-control"
                  placeholder="Isikan NIM atau NIK"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Jenis Kelamin Mahasiswa :</label>
                <select class="form-select" v-model="form.jenis_kelamin">
                  <option value="">Pilih Jenis Kelamin</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Fakultas / Prodi :</label>
                <input
                  type="text"
                  v-model="form.fakultas_prodi"
                  class="form-control"
                  :readonly="readonlyField.fakultas_prodi"
                  placeholder="Isikan Fakultas atau Program Studi"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Masa Penugasan :</label>
                <input
                  type="text"
                  v-model="form.masa_penugasan"
                  class="form-control"
                  placeholder="Contoh: Semester Ganjil 2023/2024"
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

              <div class="col-xl-12">
                <label class="form-label">Judul Skripsi :</label>
                <textarea
                  v-model="form.judul_skripsi"
                  class="form-control"
                  rows="3"
                  placeholder="Isikan Judul Skripsi Mahasiswa"
                ></textarea>
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
