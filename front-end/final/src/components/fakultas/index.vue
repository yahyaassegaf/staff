<script lang="ts" setup>
import { reactive, watch, ref, onMounted } from "vue";
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
  kode_fakultas: "",
  nama_fakultas: "",
  dekan: "",
  nidn_dekan: "",
  nidn_dekan: "",
  tanda_tangan_id: [] as any[],
  prodi: [],
};

const form = reactive({ ...defaultForm });
const listProdi = ref<any[]>([]);
const listTandaTangan = ref<any[]>([]);
const isLoadingProdi = ref(false);

async function getProdi() {
  isLoadingProdi.value = true;
  try {
    const response = await apiGet("/get-all-prodi");
    if (response.success) {
      const data = response.data?.data;
      // Ensure listProdi is always an array
      listProdi.value = Array.isArray(data) ? data : data ? [data] : [];
    }
  } catch (error) {
  } finally {
    isLoadingProdi.value = false;
  }
}

async function getTandaTangan() {
  try {
    const response = await apiGet("/tanda-tangan");
    if (response.success) {
      const data =
        response.data?.data?.data || response.data?.data || response.data;
      listTandaTangan.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
  }
}

onMounted(() => {
  getProdi();
  getTandaTangan();
});

const isLoadingData = ref(false);

watch(
  () => props.modelValue,
  async (val) => {
    if (props.isEdit && val) {
      if (!val.nama_fakultas) {
        isLoadingData.value = true;
        return;
      }

      isLoadingData.value = true;

      // Pastikan listTandaTangan sudah terisi sebelum set form
      if (listTandaTangan.value.length === 0) {
        await getTandaTangan();
      }

      // Simulasi loading untuk efek skeleton
      await new Promise((resolve) => setTimeout(resolve, 500));

      // Reset form then assign
      Object.assign(form, defaultForm);
      Object.assign(form, val);
      // Ensure form.prodi and form.tanda_tangan_id is an array for Multiselect
      if (!form.prodi) form.prodi = [];
      if (!form.tanda_tangan_id) form.tanda_tangan_id = [];

      isLoadingData.value = false;
    }
  },
  { immediate: true }
);

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
              {{ isEdit ? "Edit" : "Tambah" }} Fakultas
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-nama" class="form-label"
                  >Nama Fakultas :</label
                >
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_fakultas"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_fakultas }"
                  id="input-nama"
                  placeholder="Isikan nama fakultas"
                />
                <div v-if="errors?.nama_fakultas" class="invalid-feedback">
                  {{ errors.nama_fakultas[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-kode" class="form-label">Kode :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.kode_fakultas"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kode_fakultas }"
                  id="input-kode"
                  placeholder="Isikan kode"
                />
                <div v-if="errors?.kode_fakultas" class="invalid-feedback">
                  {{ errors.kode_fakultas[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-nama-kepala" class="form-label"
                  >Dekan :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.dekan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.dekan }"
                  id="input-nama-kepala"
                  placeholder="Isikan nama dekan"
                />
                <div v-if="errors?.dekan" class="invalid-feedback">
                  {{ errors.dekan[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label for="input-nidn-dekan" class="form-label"
                  >NIDN Dekan :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nidn_dekan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nidn_dekan }"
                  id="input-nidn-dekan"
                  placeholder="Isikan NIDN dekan"
                />
                <div v-if="errors?.nidn_dekan" class="invalid-feedback">
                  {{ errors.nidn_dekan[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Tanda Tangan (Dekan) :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  v-model="form.tanda_tangan_id"
                  :options="listTandaTangan"
                  :multiple="true"
                  :close-on-select="false"
                  :clear-on-select="false"
                  placeholder="Pilih Tanda Tangan"
                  label="nama"
                  track-by="id"
                  :class="{ 'is-invalid': errors?.tanda_tangan_id }"
                ></Multiselect>
                <div v-if="errors?.tanda_tangan_id" class="invalid-feedback">
                  {{ errors.tanda_tangan_id[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Program Studi :</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <Multiselect
                  v-else
                  v-model="form.prodi"
                  :options="listProdi"
                  :multiple="true"
                  :close-on-select="false"
                  :clear-on-select="false"
                  placeholder="Pilih Program Studi"
                  label="nama"
                  track-by="id"
                  :class="{ 'is-invalid': errors?.prodi }"
                ></Multiselect>
                <div v-if="errors?.prodi" class="text-danger small mt-1">
                  {{ errors.prodi[0] }}
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
