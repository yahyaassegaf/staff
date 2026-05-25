<script lang="ts" setup>
import { reactive, watch, ref, onMounted } from "vue";
import { apiGet } from "../../services/api/request";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";

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
  nama: "",
  nim: "",
  nik: "",
  tgl_lahir: "",
  nilai_akreditasi: "",
  nomor_sk_ban_pt: "",
  nomor_ijazah_nasional: "",
  tanggal_sk_yudisium: "",
  tanggal_penerbitan: "",
  prodi_id: null as number | null,
  status: "belum",
};

const form = reactive({ ...defaultForm });
const isLoadingData = ref(false);
const prodiOptions = ref<any[]>([]);

// Load prodi options
const loadProdi = async () => {
  try {
    const response = await apiGet('/get-all-prodi');
    if (response.success) {
      const data = response.data?.data || [];
      prodiOptions.value = Array.isArray(data) ? data : [];
    }
  } catch (error) {
  }
};

watch(
  () => props.modelValue,
  async (val) => {
    if (props.isEdit && val) {
      if (!val.nama) {
        isLoadingData.value = true;
        return;
      }

      isLoadingData.value = true;
      await new Promise((resolve) => setTimeout(resolve, 500));

      Object.assign(form, defaultForm);
      Object.assign(form, val);

      if (form.prodi_id) {
        form.prodi_id = Number(form.prodi_id);
      }

      isLoadingData.value = false;
    }
  },
  { immediate: true }
);

const emit = defineEmits(["submit"]);

function submitForm() {
  emit("submit", form);
}

onMounted(() => {
  loadProdi();
});
</script>

<template>
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">{{ isEdit ? "Edit" : "Tambah" }} Mahasiswa</div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <!-- Nama -->
              <div class="col-xl-12">
                <label for="input-nama" class="form-label">Nama Mahasiswa :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama }"
                  id="input-nama"
                  placeholder="Masukkan nama mahasiswa"
                />
                <div v-if="errors?.nama" class="invalid-feedback">
                  {{ errors.nama[0] }}
                </div>
              </div>

              <!-- NIM -->
              <div class="col-xl-6">
                <label for="input-nim" class="form-label">NIM :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nim"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nim }"
                  id="input-nim"
                  placeholder="Masukkan NIM"
                />
                <div v-if="errors?.nim" class="invalid-feedback">
                  {{ errors.nim[0] }}
                </div>
              </div>

              <!-- NIK -->
              <div class="col-xl-6">
                <label for="input-nik" class="form-label">NIK :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="number"
                  v-model="form.nik"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nik }"
                  id="input-nik"
                  placeholder="Masukkan NIK"
                />
                <div v-if="errors?.nik" class="invalid-feedback">
                  {{ errors.nik[0] }}
                </div>
              </div>

              <!-- Tanggal Lahir -->
              <div class="col-xl-6">
                <label for="input-tgl-lahir" class="form-label">Tanggal Lahir :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tgl_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tgl_lahir }"
                  id="input-tgl-lahir"
                />
                <div v-if="errors?.tgl_lahir" class="invalid-feedback">
                  {{ errors.tgl_lahir[0] }}
                </div>
              </div>

              <!-- Prodi -->
              <div class="col-xl-6">
                <label for="input-prodi" class="form-label">Program Studi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  v-model="form.prodi_id"
                  :options="prodiOptions"
                  :multiple="false"
                  :close-on-select="true"
                  :clear-on-select="true"
                  placeholder="Pilih prodi"
                  label="nama"
                  track-by="id"
                  :class="{ 'is-invalid': errors?.prodi_id }"
                ></Multiselect>
                <div v-if="errors?.prodi_id" class="text-danger small mt-1">
                  {{ errors.prodi_id[0] }}
                </div>
              </div>

              <!-- Nilai Akreditasi -->
              <div class="col-xl-6">
                <label for="input-nilai-akreditasi" class="form-label">Nilai Akreditasi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nilai_akreditasi"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nilai_akreditasi }"
                  id="input-nilai-akreditasi"
                  placeholder="Masukkan nilai akreditasi"
                />
                <div v-if="errors?.nilai_akreditasi" class="invalid-feedback">
                  {{ errors.nilai_akreditasi[0] }}
                </div>
              </div>

              <!-- Nomor SK BAN-PT -->
              <div class="col-xl-6">
                <label for="input-nomor-sk-ban-pt" class="form-label">Nomor SK BAN-PT :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nomor_sk_ban_pt"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nomor_sk_ban_pt }"
                  id="input-nomor-sk-ban-pt"
                  placeholder="Masukkan nomor SK BAN-PT"
                />
                <div v-if="errors?.nomor_sk_ban_pt" class="invalid-feedback">
                  {{ errors.nomor_sk_ban_pt[0] }}
                </div>
              </div>

              <!-- Nomor Ijazah Nasional -->
              <div class="col-xl-6">
                <label for="input-nomor-ijazah-nasional" class="form-label">Nomor Ijazah Nasional :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nomor_ijazah_nasional"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nomor_ijazah_nasional }"
                  id="input-nomor-ijazah-nasional"
                  placeholder="Masukkan nomor ijazah nasional"
                />
                <div v-if="errors?.nomor_ijazah_nasional" class="invalid-feedback">
                  {{ errors.nomor_ijazah_nasional[0] }}
                </div>
              </div>

              <!-- Tanggal SK Yudisium -->
              <div class="col-xl-6">
                <label for="input-tanggal-sk-yudisium" class="form-label">Tanggal SK Yudisium :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal_sk_yudisium"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_sk_yudisium }"
                  id="input-tanggal-sk-yudisium"
                />
                <div v-if="errors?.tanggal_sk_yudisium" class="invalid-feedback">
                  {{ errors.tanggal_sk_yudisium[0] }}
                </div>
              </div>

              <!-- Tanggal Penerbitan -->
              <div class="col-xl-6">
                <label for="input-tanggal-penerbitan" class="form-label">Tanggal Penerbitan :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal_penerbitan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_penerbitan }"
                  id="input-tanggal-penerbitan"
                />
                <div v-if="errors?.tanggal_penerbitan" class="invalid-feedback">
                  {{ errors.tanggal_penerbitan[0] }}
                </div>
              </div>

              <!-- Status -->
              <div class="col-xl-6">
                <label for="input-status" class="form-label">Status :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  class="form-select"
                  :class="{ 'is-invalid': errors?.status }"
                  v-model="form.status"
                  id="input-status"
                >
                  <option value="belum">Belum</option>
                  <option value="sudah">Sudah</option>
                </select>
                <div v-if="errors?.status" class="invalid-feedback">
                  {{ errors.status[0] }}
                </div>
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
