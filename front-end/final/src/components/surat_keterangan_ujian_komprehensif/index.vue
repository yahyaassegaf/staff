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
const listMhs = ref<any>(null);

watch(listMhs, (val) => {
  console.log(listMhs);

  if (!val) {
    return;
  }

  form.nama_mhs = val.nama;

  form.nim = val.nim;

  form.tanggal_lahir = val.tanggal_lahir;

  form.tempat_lahir = val.tempat_lahir;

  form.prodi_mhs = val.alias_prodi;

  form.alamat_rumah = val.alamat;

  form.koordinator_kompre = val.koor_komprehensif;
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
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Koordinator Komprehensif:</label
                >
                <input
                  type="text"
                  v-model="form.koordinator_kompre"
                  class="form-control"
                  :readonly="readonlyField.koordinator_kompre"
                  id="input-kode"
                  placeholder="Isikan kode"
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
                  label="nama"
                  @search-change="getMhs"
                  track-by="id"
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
                  placeholder="Isikan kode"
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
                  placeholder="Isikan tanggal lahir"
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
                  placeholder="Isikan nama tanggal lahir"
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
                  placeholder="Isikan kelas pondok"
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
                  placeholder="Isikan kelas pondok"
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

<style scoped></style>
