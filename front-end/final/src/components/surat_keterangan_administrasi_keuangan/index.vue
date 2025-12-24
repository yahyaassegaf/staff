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
  no_surat: "",
  kepala_biro: null,
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
};

const readonlyField = ref({
  nama_mhs: false,
  tanggal_lahir: false,
  tempat_lahir: false,
  kelas_pondok: false,
  alamat_rumah: false,
  prodi_mhs: false,
  kepala_biro: false,
});

const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, (val) => {
  console.log(listMhs);

  if (!val) return;

  if (val.nama) {
    form.nama_mhs = val.nama;
    readonlyField.value.nama_mhs = true;
  } else {
    readonlyField.value.nama_mhs = false;
  }
  form.nim = val.nim;

  if (val.tanggal_lahir) {
    form.tanggal_lahir = val.tanggal_lahir;
    readonlyField.value.tanggal_lahir = true;
  } else {
    readonlyField.value.tanggal_lahir = false;
  }

  if (val.tempat_lahir) {
    form.tempat_lahir = val.tempat_lahir;
    readonlyField.value.tempat_lahir = true;
  } else {
    readonlyField.value.tempat_lahir = false;
  }

  if (val.alias_prodi) {
    form.prodi_mhs = val.alias_prodi;
    readonlyField.value.prodi_mhs = true;
  } else {
    readonlyField.value.prodi_mhs = false;
  }

  if (val.alamat) {
    form.alamat_rumah = val.alamat;
    readonlyField.value.alamat_rumah = true;
  } else {
    readonlyField.value.alamat_rumah = false;
  }
});

const listProdi = ref<any[]>([]);

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

onMounted(() => {
  getProdi();
});

function customName(params: any) {
  return `${params.nama} - ${params.nim}`;
}

watch(
  () => props.modelValue,
  (val) => {
    console.log(val);
    if (props.isEdit && val) {
      Object.assign(form, val);
      if (form.nim) {
        listMhs.value = {
          nim: form.nim,
          nama: form.nama_mhs,
          // Add other fields if necessary for consistency, though nim/nama might be enough for display
          id: form.nim, // assuming id tracking uses nim or similar, user said "sesuaikan dengan form.nim"
        };
        // To ensure options are populated so the selected value appears correctly if it relies on options
        options.value = [listMhs.value];
      }
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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Keterangan Administrasi
              Keuangan
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Program Studi:</label
                >
                <select class="form-select" v-model="form.prodi_id">
                  <option value="0">Pilih Prodi</option>
                  <option
                    v-for="prodi in listProdi"
                    :key="prodi.id"
                    :value="Number(prodi.id)"
                  >
                    {{ prodi.nama }}
                  </option>
                </select>
              </div>
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Kepala Biro:</label
                >
                <input
                  type="text"
                  v-model="form.kepala_biro"
                  class="form-control"
                  :readonly="readonlyField.kepala_biro"
                  id="input-kepala-biro"
                  placeholder="Isikan Nama Kepala Biro"
                />
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
                  :label="nama"
                  @search-change="getMhs"
                  :track-by="id"
                  :searchable="true"
                  :loading="loading"
                  :custom-label="customName"
                ></Multiselect>
              </div>
              <div class="col-xl-6">
                <label for="input-kode" class="form-label"
                  >Nama Mahasiswa :</label
                >
                <input
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :readonly="readonlyField.nama_mhs"
                  id="input-kode"
                  placeholder="Isikan nama mahasiswa"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Tempat Lahir:</label
                >
                <input
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  :readonly="readonlyField.tempat_lahir"
                  id="input-nama-kepala"
                  placeholder="Isikan tempat lahir"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Tanggal Lahir:</label
                >
                <input
                  type="date"
                  v-model="form.tanggal_lahir"
                  :readonly="readonlyField.tanggal_lahir"
                  class="form-control"
                  id="input-nama-kepala"
                  placeholder="Isikan tanggal lahir"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Prodi Mahasiswa:</label
                >
                <input
                  type="text"
                  v-model="form.prodi_mhs"
                  :readonly="readonlyField.prodi_mhs"
                  class="form-control"
                  id="input-nama-kepala"
                  placeholder="Isikan prodi mahasiswa"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Kelas Pondok:</label
                >
                <input
                  type="text"
                  v-model="form.kelas_pondok"
                  class="form-control"
                  id="input-nama-kepala"
                  placeholder="Isikan kelas pondok"
                />
              </div>
              <div class="col-xl-6">
                <label for="input-nama-kepala" class="form-label"
                  >Tanggal Surat:</label
                >
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  id="input-nama-kepala"
                  placeholder="Isikan tanggal surat"
                />
              </div>
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >alamat:</label
                >
                <textarea
                  v-model="form.alamat_rumah"
                  class="form-control"
                  :readonly="readonlyField.alamat_rumah"
                  id="input-nama-kepala"
                  placeholder="Isikan alamat"
                ></textarea>
              </div>
            </div>
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

<style scoped></style>
