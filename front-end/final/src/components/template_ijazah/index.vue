<script lang="ts" setup>
import { reactive, watch, ref, onMounted, computed } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import { apiGet } from "../../services/api/request";

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
  prodi_id: null as number | null,
  jenjang: "",
  nama_template: "",
  file_background: "",
  ukuran_kertas: "A4",
  orientasi: "portrait",
  is_active: "aktif",
};

const form = reactive({ ...defaultForm });
const isLoadingData = ref(false);
const prodiOptions = ref<any[]>([]);
const selectedFile = ref<File | null>(null);
const previewUrl = ref<string>("");
const fileInputRef = ref<HTMLInputElement | null>(null);

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

// Computed property for Multiselect to handle ID or Object
const selectedProdiValue = computed({
  get() {
    if (!form.prodi_id) return null;
    
    // If it's already an object (from user selection or previous sync)
    if (typeof form.prodi_id === 'object' && form.prodi_id !== null) {
      return form.prodi_id;
    }
    
    // If it's an ID, find the object in options
    if (prodiOptions.value.length > 0) {
      const found = prodiOptions.value.find(p => Number(p.id) === Number(form.prodi_id));
      if (found) return found;
    }
    
    // Fallback: Use the nama_prodi from props if available to show the name immediately
    if (props.modelValue && (props.modelValue as any).nama_prodi) {
      return {
        id: form.prodi_id,
        nama: (props.modelValue as any).nama_prodi
      };
    }
    
    return null;
  },
  set(val) {
    // When user selects a value in Multiselect
    form.prodi_id = val;
  }
});

watch(
  () => props.modelValue,
  async (val) => {
    if (props.isEdit && val) {
      if (!val.nama_template && val.id === "") {
        isLoadingData.value = true;
        return;
      }

      isLoadingData.value = true;
      // Reset form then assign
      Object.assign(form, defaultForm);
      Object.assign(form, val);

      // Set preview URL if file_background exists
      if (form.file_background) {
        previewUrl.value = form.file_background;
      }

      isLoadingData.value = false;
    }
  },
  { immediate: true }
);

const emit = defineEmits(["submit"]);

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  
  if (file) {
    // Validate file type
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
      alert('File harus berupa gambar (JPG, PNG, GIF, atau WebP)');
      if (fileInputRef.value) {
        fileInputRef.value.value = '';
      }
      return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert('Ukuran file maksimal 5MB');
      if (fileInputRef.value) {
        fileInputRef.value.value = '';
      }
      return;
    }

    selectedFile.value = file;
    
    // Create preview URL
    const reader = new FileReader();
    reader.onload = (e) => {
      previewUrl.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
}

function removeFile() {
  selectedFile.value = null;
  previewUrl.value = '';
  form.file_background = '';
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
}

function submitForm() {
  
  // Extract ID for the payload
  let prodiId = null;
  if (form.prodi_id) {
    if (typeof form.prodi_id === 'object' && form.prodi_id.id) {
      prodiId = Number(form.prodi_id.id);
    } else {
      prodiId = Number(form.prodi_id);
    }
  }

  // Create a clean payload for the parent
  const payload = {
    ...form,
    prodi_id: prodiId,
    selectedFile: selectedFile.value
  };
  
  emit("submit", payload);
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
            <div class="card-title">{{ isEdit ? "Edit" : "Tambah" }} Template Ijazah</div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <!-- Template Name -->
              <div class="col-xl-12">
                <label for="input-nama-template" class="form-label">Nama Template :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_template"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_template }"
                  id="input-nama-template"
                  placeholder="Masukkan nama template"
                />
                <div v-if="errors?.nama_template" class="invalid-feedback">
                  {{ errors.nama_template[0] }}
                </div>
              </div>

              <!-- Prodi -->
              <div class="col-xl-6">
                <label for="input-prodi" class="form-label">Program Studi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  v-model="selectedProdiValue"
                  :options="prodiOptions"
                  :multiple="false"
                  :close-on-select="true"
                  :clear-on-select="true"
                  placeholder="Pilih prodi (kosongkan untuk global)"
                  label="nama"
                  track-by="id"
                  :class="{ 'is-invalid': errors?.prodi_id }"
                ></Multiselect>
                <div v-if="errors?.prodi_id" class="text-danger small mt-1">
                  {{ errors.prodi_id[0] }}
                </div>
                <small class="text-muted">Kosongkan untuk template global (semua prodi)</small>
              </div>

              <!-- Jenjang -->
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
                  <option value="">Semua Jenjang</option>
                  <option value="D3">D3</option>
                  <option value="D4">D4</option>
                  <option value="S1">S1</option>
                  <option value="S2">S2</option>
                  <option value="S3">S3</option>
                </select>
                <div v-if="errors?.jenjang" class="invalid-feedback">
                  {{ errors.jenjang[0] }}
                </div>
                <small class="text-muted">Kosongkan untuk semua jenjang</small>
              </div>

              <!-- Ukuran Kertas -->
              <div class="col-xl-6">
                <label for="input-ukuran-kertas" class="form-label">Ukuran Kertas :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  class="form-select"
                  :class="{ 'is-invalid': errors?.ukuran_kertas }"
                  v-model="form.ukuran_kertas"
                  id="input-ukuran-kertas"
                >
                  <option value="A4">A4</option>
                  <option value="A3">A3</option>
                  <option value="Legal">Legal</option>
                  <option value="F4">F4</option>
                </select>
                <div v-if="errors?.ukuran_kertas" class="invalid-feedback">
                  {{ errors.ukuran_kertas[0] }}
                </div>
              </div>

              <!-- Orientasi -->
              <div class="col-xl-6">
                <label for="input-orientasi" class="form-label">Orientasi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  class="form-select"
                  :class="{ 'is-invalid': errors?.orientasi }"
                  v-model="form.orientasi"
                  id="input-orientasi"
                >
                  <option value="portrait">Portrait (Potret)</option>
                  <option value="landscape">Landscape (Lanskap)</option>
                </select>
                <div v-if="errors?.orientasi" class="invalid-feedback">
                  {{ errors.orientasi[0] }}
                </div>
              </div>

              <!-- File Background -->
              <div class="col-xl-12">
                <label for="input-file-background" class="form-label">File Background :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <div v-else>
                  <div class="row">
                    <div class="col-md-12">
                      <input
                        ref="fileInputRef"
                        type="file"
                        class="form-control"
                        :class="{ 'is-invalid': errors?.file_background }"
                        id="input-file-background"
                        accept="image/*"
                        @change="handleFileChange"
                      />
                      <div v-if="errors?.file_background" class="invalid-feedback">
                        {{ errors.file_background[0] }}
                      </div>
                      <small class="text-muted d-block mt-1">
                        Format: JPG, PNG, GIF, WebP (Max 5MB)
                      </small>
                    </div>
                    <div class="col-md-4" v-if="previewUrl">
                      <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        @click="removeFile"
                      >
                        <i class="ri-close-line"></i> Hapus
                      </button>
                    </div>
                  </div>

                  <!-- Preview -->
                  <div v-if="previewUrl" class="mt-3">
                    <label class="form-label small">Preview:</label>
                    <div class="border rounded p-2 bg-light text-center">
                      <img
                        :src="previewUrl"
                        alt="Preview Background"
                        class="img-fluid"
                        style="max-height: 200px; object-fit: contain;"
                      />
                    </div>
                    <small class="text-muted">
                      {{ selectedFile ? selectedFile.name : 'File dari server' }}
                      <span v-if="selectedFile">
                        ({{ (selectedFile.size / 1024).toFixed(2) }} KB)
                      </span>
                    </small>
                  </div>
                </div>
              </div>

              <!-- Status Aktif -->
              <div class="col-xl-12">
                <label for="input-is-active" class="form-label">Status :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  class="form-select"
                  :class="{ 'is-invalid': errors?.is_active }"
                  v-model="form.is_active"
                  id="input-is-active"
                >
                  <option value="aktif">Aktif</option>
                  <option value="tidak">Tidak Aktif</option>
                </select>
                <div v-if="errors?.is_active" class="invalid-feedback">
                  {{ errors.is_active[0] }}
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
