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
  prodi_id: 0,
  nama_mhs: "",
  nim: "",
  nik: "",
  tempat_lahir: "",
  tanggal_lahir: "",
  prodi_mhs: "",
  semester: "",
  tahun_akademik: "",
  nama_ortu: "",
  nik_ortu: "",
  nip_ortu: "",
  alamat_ortu: "",
  hp_ortu: "",
  tanggal: "",
};

const isFromSystem = ref(true);

const readonlyField = ref({
  nama_mhs: false,
  tanggal_lahir: false,
  tempat_lahir: false,
  prodi_mhs: false,
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

  if (val.alias_prodi) {
    form.prodi_mhs = val.alias_prodi;
    readonlyField.value.prodi_mhs = true;
  } else {
    form.prodi_mhs = val.prodi_mhs;
    readonlyField.value.prodi_mhs = false;
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
    form.nomor_surat = val.nomor_surat ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.nama_mhs = val.nama_lengkap ?? "";
    form.nim = val.nim ?? "";
    form.nik = val.nik ?? "";
    form.tempat_lahir = val.tempat_lahir ?? "";
    form.tanggal_lahir = val.tanggal_lahir
      ? val.tanggal_lahir.slice(0, 10)
      : "";
    form.prodi_mhs = val.prodi_mhs ?? "";
    form.semester = val.semester ?? "";
    form.tahun_akademik = val.tahun_akademik ?? "";
    form.nama_ortu = val.nama_ortu ?? "";
    form.nik_ortu = val.nik_ortu ?? "";
    form.nip_ortu = val.nip_ortu ?? "";
    form.alamat_ortu = val.alamat_ortu ?? "";
    form.hp_ortu = val.hp_ortu ?? "";
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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Keterangan Aktif Mahasiswa
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
                <label class="form-label">NIK Mahasiswa :</label>
                <input
                  type="text"
                  v-model="form.nik"
                  class="form-control"
                  placeholder="Isikan NIK"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tempat Lahir:</label>
                <input
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  placeholder="Isikan Tempat Lahir"
                />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tanggal Lahir:</label>
                <input
                  type="date"
                  v-model="form.tanggal_lahir"
                  class="form-control"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Program Studi Mahasiswa:</label>
                <input
                  type="text"
                  v-model="form.prodi_mhs"
                  class="form-control"
                  :readonly="readonlyField.prodi_mhs"
                  placeholder="Isikan Program Studi"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Semester:</label>
                <input
                  type="text"
                  v-model="form.semester"
                  class="form-control"
                  placeholder="Isikan Semester (Contoh: V / Lima)"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tahun Akademik:</label>
                <input
                  type="text"
                  v-model="form.tahun_akademik"
                  class="form-control"
                  placeholder="Isikan Tahun Akademik (Contoh: 2024/2025)"
                />
              </div>

              <hr />
              <div class="card-title mb-0">Data Orang Tua</div>

              <div class="col-xl-4">
                <label class="form-label">Nama Orang Tua:</label>
                <input
                  type="text"
                  v-model="form.nama_ortu"
                  class="form-control"
                  placeholder="Isikan Nama Orang Tua"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIK Orang Tua:</label>
                <input
                  type="text"
                  v-model="form.nik_ortu"
                  class="form-control"
                  placeholder="Isikan NIK Orang Tua"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">NIP Orang Tua (Opsional):</label>
                <input
                  type="text"
                  v-model="form.nip_ortu"
                  class="form-control"
                  placeholder="Isikan NIP (Jika PNS)"
                />
              </div>

              <div class="col-xl-8">
                <label class="form-label">Alamat Orang Tua:</label>
                <textarea
                  v-model="form.alamat_ortu"
                  class="form-control"
                  rows="2"
                  placeholder="Isikan Alamat Lengkap Orang Tua"
                ></textarea>
              </div>

              <div class="col-xl-4">
                <label class="form-label">No. HP / Kontak Ortu:</label>
                <input
                  type="text"
                  v-model="form.hp_ortu"
                  class="form-control"
                  placeholder="Isikan No. HP Orang Tua"
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tanggal Surat:</label>
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
