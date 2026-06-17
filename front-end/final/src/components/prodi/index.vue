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
  kode: "",
  alias: "",
  nama: "",
  aktif: "T",
  jenjang: "S1",
  gelar: "",
  nidn_kepala: "",
  nama_kepala: "",
  tanda_tangan: null as {id: number, nama: string, tdd: string} | null,
};

const form = reactive({ ...defaultForm });
const isLoadingData = ref(false);
const tandaTanganOptions = ref<any[]>([]);

// Load tanda_tangan options
const loadTandaTangan = async () => {
  try {
    const response = await apiGet('/get-all-tanda-tangan');
    
    if (response.success) {
      // Handle the actual response structure: {success: true, data: {data: [], message: "...", status: true}}
      const data = response.data?.data || [];
      
      // Ensure tandaTanganOptions is always an array
      tandaTanganOptions.value = Array.isArray(data) ? data : [];
    } else {
      if (response.error === 'Unauthorized') {
      }
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
      // Simulasi loading
      await new Promise((resolve) => setTimeout(resolve, 500));

      // Reset form then assign
      Object.assign(form, defaultForm);
      Object.assign(form, val);
      
      // Ensure form.tanda_tangan is properly set for single select
      if (!form.tanda_tangan) form.tanda_tangan = null;

      isLoadingData.value = false;
    }
  },
  { immediate: true }
);

const emit = defineEmits(["submit"]);

function submitForm() {
  // Convert selected object to ID for API
  const formData = {
    ...form,
    tanda_tangan: form.tanda_tangan ? form.tanda_tangan.id : null
  };
  emit("submit", formData);
}

onMounted(() => {
  loadTandaTangan();
});
</script>

<template>
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">{{ isEdit ? "Edit" : "Tambah" }} Prodi</div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-nama" class="form-label">Nama Prodi :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama }"
                  id="input-nama"
                  placeholder="Isikan nama prodi"
                />
                <div v-if="errors?.nama" class="invalid-feedback">
                  {{ errors.nama[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-kode" class="form-label">Kode :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.kode"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kode }"
                  id="input-kode"
                  placeholder="Isikan kode"
                />
                <div v-if="errors?.kode" class="invalid-feedback">
                  {{ errors.kode[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-alias" class="form-label">Alias :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.alias"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alias }"
                  id="input-alias"
                  maxlength="5"
                  placeholder="Isikan alias"
                />
                <div v-if="errors?.alias" class="invalid-feedback">
                  {{ errors.alias[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-jenjang" class="form-label">Jenjang :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  class="form-select"
                  :class="{ 'is-invalid': errors?.jenjang }"
                  v-model="form.jenjang"
                  id="input-jenjang"
                  aria-label="Pilih jenjang"
                >
                  <option value="S1">S1</option>
                  <option value="S2">S2</option>
                  <option value="S3">S3</option>
                </select>
                <div v-if="errors?.jenjang" class="invalid-feedback">
                  {{ errors.jenjang[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-gelar" class="form-label">Gelar :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.gelar"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.gelar }"
                  id="input-gelar"
                  maxlength="50"
                  placeholder="Isikan gelar lulusan"
                />
                <div v-if="errors?.gelar" class="invalid-feedback">
                  {{ errors.gelar[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nidn-kepala" class="form-label"
                  >NIDN Kepala :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nidn_kepala"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nidn_kepala }"
                  id="input-nidn-kepala"
                  maxlength="15"
                  placeholder="Isikan NIDN kepala prodi"
                />
                <div v-if="errors?.nidn_kepala" class="invalid-feedback">
                  {{ errors.nidn_kepala[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-tanda-tangan" class="form-label"
                  >Tanda Tangan :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  v-model="form.tanda_tangan"
                  :options="tandaTanganOptions"
                  :multiple="false"
                  :close-on-select="true"
                  :clear-on-select="true"
                  placeholder="Pilih tanda tangan"
                  label="nama"
                  track-by="id"
                  :class="{ 'is-invalid': errors?.tanda_tangan }"
                ></Multiselect>
                <div v-if="errors?.tanda_tangan" class="text-danger small mt-1">
                  {{ errors.tanda_tangan[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Kepala Prodi :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_kepala"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_kepala }"
                  id="input-nama-kepala"
                  placeholder="Isikan nama kepala prodi"
                />
                <div v-if="errors?.nama_kepala" class="invalid-feedback">
                  {{ errors.nama_kepala[0] }}
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
