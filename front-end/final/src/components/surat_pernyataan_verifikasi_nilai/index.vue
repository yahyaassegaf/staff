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

const isFromSystem = ref(true);

const readonlyField = ref({
  nama_mhs: false,
  prodi: false,
  fakultas: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, (val) => {
  if (!val) return;

  if (isFromSystem.value) return;
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
    form.nama_penandatangan = val.nama_penandatangan ?? "";
    form.niy = val.niy ?? "";
    form.jabatan = val.jabatan ?? "";
    form.nama_mhs = val.nama_mahasiswa ?? "";
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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Pernyataan Melakukan
              Verifikasi Nilai Mahasiswa
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
              <div class="card-title mb-0">Informasi Penandatangan</div>

              <div class="col-xl-4">
                <label class="form-label">Nama yang bertanda tangan :</label>
                <input
                  type="text"
                  v-model="form.nama_penandatangan"
                  class="form-control"
                  placeholder="Isikan Nama Penandatangan"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIY :</label>
                <input
                  type="text"
                  v-model="form.niy"
                  class="form-control"
                  placeholder="Isikan NIY"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Jabatan :</label>
                <input
                  type="text"
                  v-model="form.jabatan"
                  class="form-control"
                  placeholder="Isikan Jabatan"
                />
              </div>

              <hr />
              <div class="card-title mb-0">Data Mahasiswa</div>

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
                <label class="form-label">Program Studi :</label>
                <input
                  type="text"
                  v-model="form.prodi"
                  class="form-control"
                  :readonly="readonlyField.prodi"
                  placeholder="Isikan Program Studi"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Fakultas :</label>
                <input
                  type="text"
                  v-model="form.fakultas"
                  class="form-control"
                  :readonly="readonlyField.fakultas"
                  placeholder="Isikan Fakultas"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tanggal :</label>
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
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
