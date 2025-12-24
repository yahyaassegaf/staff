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
  nomor_surat: "",
  ketua: "",
  prodi_id: 0,
  nama_lengkap: null,
  nama_mhs: "",
  tanggal_lahir: "",
  tempat_lahir: "",
  nim: "",
  jenis_kelamin: "",
  prodi_mhs: "",
  alamat_rumah: "",
  kelas_pondok: "",
  tanggal_berlaku_dari: "",
  tanggal_berlaku_sampai: "",
  tanggal: "",
};

const isFromSystem = ref(true);

const readonlyField = ref({
  nama_mhs: false,
  tanggal_lahir: false,
  tempat_lahir: false,
  jenis_kelamin: false,
  prodi_mhs: false,
  alamat_rumah: false,
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
  form.tanggal_lahir = val.tanggal_lahir ? val.tanggal_lahir.slice(0, 10) : "";
  form.tempat_lahir = val.tempat_lahir;
  form.jenis_kelamin = val.jenis_kelamin || "";

  if (val.alias_prodi) {
    form.prodi_mhs = val.alias_prodi;
    readonlyField.value.prodi_mhs = true;
  } else {
    form.prodi_mhs = val.prodi_mhs;
    readonlyField.value.prodi_mhs = false;
  }

  form.alamat_rumah = val.alamat;
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
    form.nomor_surat = val.nomor_surat ?? "";
    form.ketua = val.ketua ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.nama_mhs = val.nama_lengkap ?? "";
    form.nim = val.nim ?? "";
    form.jenis_kelamin = val.jenis_kelamin ?? "";
    form.prodi_mhs = val.prodi_mhs ?? "";
    form.alamat_rumah = val.alamat_rumah ?? "";
    form.tempat_lahir = val.tempat_lahir ?? "";
    form.kelas_pondok = val.kelas_pondok ?? "";

    form.tanggal_lahir = val.tanggal_lahir
      ? val.tanggal_lahir.slice(0, 10)
      : "";
    form.tanggal_berlaku_dari = val.tanggal_berlaku_dari
      ? val.tanggal_berlaku_dari.slice(0, 10)
      : "";
    form.tanggal_berlaku_sampai = val.tanggal_berlaku_sampai
      ? val.tanggal_berlaku_sampai.slice(0, 10)
      : "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";

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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Keterangan Qismul Aman
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <!-- Program Studi -->
              <div class="col-xl-12">
                <label class="form-label">Program Studi:</label>
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

              <!-- NIM Mahasiswa (Search) -->
              <div class="col-xl-12">
                <label class="form-label">Nim Mahasiswa :</label>
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

              <!-- Nama Mahasiswa -->
              <div class="col-xl-6">
                <label class="form-label">Nama Mahasiswa :</label>
                <input
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :readonly="readonlyField.nama_mhs"
                  placeholder="Isikan Nama Mahasiswa"
                />
              </div>

              <!-- Tempat Lahir -->
              <div class="col-xl-6">
                <label class="form-label">Tempat Lahir:</label>
                <input
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  placeholder="Isikan Tempat Lahir"
                />
              </div>

              <!-- Tanggal Lahir -->
              <div class="col-xl-6">
                <label class="form-label">Tanggal Lahir:</label>
                <input
                  type="date"
                  v-model="form.tanggal_lahir"
                  class="form-control"
                />
              </div>

              <!-- Jenis Kelamin -->
              <div class="col-xl-6">
                <label class="form-label">Jenis Kelamin:</label>
                <select class="form-select" v-model="form.jenis_kelamin">
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
                </select>
              </div>

              <!-- Prodi Mahasiswa -->
              <div class="col-xl-6">
                <label class="form-label">Prodi Mahasiswa:</label>
                <input
                  type="text"
                  v-model="form.prodi_mhs"
                  class="form-control"
                  :readonly="readonlyField.prodi_mhs"
                  placeholder="Isikan Prodi Mahasiswa"
                />
              </div>

              <!-- Kelas Pondok -->
              <div class="col-xl-6">
                <label class="form-label">Kelas Pondok:</label>
                <input
                  type="text"
                  v-model="form.kelas_pondok"
                  class="form-control"
                  placeholder="Isikan Kelas Pondok"
                />
              </div>

              <!-- Ketua Qismul Aman -->
              <div class="col-xl-12">
                <label class="form-label">Ketua Qismul Aman Ma’had:</label>
                <input
                  type="text"
                  v-model="form.ketua"
                  class="form-control"
                  placeholder="Isikan Nama Ketua"
                />
              </div>

              <!-- Masa Berlaku -->
              <div class="col-xl-6">
                <label class="form-label">Tanggal Berlaku Dari:</label>
                <input
                  type="date"
                  v-model="form.tanggal_berlaku_dari"
                  class="form-control"
                />
              </div>
              <div class="col-xl-6">
                <label class="form-label">Tanggal Berlaku Sampai:</label>
                <input
                  type="date"
                  v-model="form.tanggal_berlaku_sampai"
                  class="form-control"
                />
              </div>

              <!-- Tanggal Surat -->
              <div class="col-xl-6">
                <label class="form-label">Tanggal Surat:</label>
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                />
              </div>

              <!-- Alamat -->
              <div class="col-xl-12">
                <label class="form-label">Alamat:</label>
                <textarea
                  v-model="form.alamat_rumah"
                  class="form-control"
                  placeholder="Isikan Alamat"
                ></textarea>
              </div>
            </div>
          </div>
          <div class="card-footer text-end">
            <button class="btn btn-primary-light btn-wave">
              {{ isEdit ? "Update" : "Simpan" }}
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
