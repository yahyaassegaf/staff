<script lang="ts" setup>
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
import "@yaireo/tagify/dist/tagify.css";
import { onMounted, ref, reactive, watch } from "vue";
import Pageheader from "../../shared/components/pageheader/pageheader.vue";
import { apiGet } from "../../services/api/request";

const levels = ref<level[]>([]);
const prodis = ref<prodi[]>([]);
const loadingLists = ref(false);

async function fetchLists() {
  try {
    loadingLists.value = true;
    const [levelRes, prodiRes] = await Promise.all([
      apiGet("/get-level"),
      apiGet("/get-prodi"),
    ]);

    if (levelRes.success) {
      const data = levelRes.data?.data;
      levels.value = Array.isArray(data) ? data : [data];
    }

    if (prodiRes.success) {
      const data = prodiRes.data?.data;
      prodis.value = Array.isArray(data) ? data : [data];

      // If there's only one prodi, auto-select it as in the example
      if (prodis.value.length === 1 && !props.isEdit) {
        form.prodi_id = prodis.value[0].id;
      }
    }
  } catch (error) {
  } finally {
    loadingLists.value = false;
  }
}

onMounted(() => {
  fetchLists();
});
// interface prodi {
//   id: string;
//   nama: string;
// }

interface level {
  id: string;
  nama: string;
}

interface prodi {
  id: string;
  nama: string;
}

// const props = defineProps({
//   modelValue: Object,
//   isEdit: Boolean,
// });
const props = defineProps<{
  modelValue: any;
  isEdit: boolean;
  title?: string;
  errors?: any;
}>();

const defaultForm = {
  name: "",
  email: "",
  handphone: "",
  level_id: "",
  prodi_id: "",
  password: "",
  jenis_kelamin: "",
  id: "",
};

const form = reactive({ ...defaultForm });

watch(
  () => props.modelValue,
  (val) => {
    if (props.isEdit && val) {
      Object.assign(form, val);
    }
  },
  { immediate: true }
);

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement;

  if (target.files && target.files.length > 0) {
    emit("file-change", target.files[0]); // kirim File object
  }
}

const emit = defineEmits(["submit", "file-change"]);

function submitForm() {
  emit("submit", form);
}
</script>

<template>
  <Pageheader />
  <!-- Start::row-1 -->
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">
              {{ props.title || (isEdit ? "Edit User" : "Tambah Users") }}
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-label" class="form-label">Username :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <input
                  type="text"
                  v-model="form.name"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.name }"
                  id="input-label"
                  placeholder="isikan username"
                />
                <div v-if="errors?.name" class="invalid-feedback">
                  {{ errors.name[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-label11" class="form-label">Level :</label>
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.level_id }"
                  v-model="form.level_id"
                  aria-label="Default select example"
                >
                  <option selected>Select Level..</option>
                  <option
                    v-for="level in levels || []"
                    :key="level?.id"
                    :value="level?.id"
                  >
                    {{ level?.nama }}
                  </option>
                </select>
                <div v-if="errors?.level_id" class="invalid-feedback">
                  {{ errors.level_id[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-label11" class="form-label">Prodi :</label>
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.prodi_id }"
                  v-model="form.prodi_id"
                  aria-label="Default select example"
                >
                  <option selected>Select Prodi..</option>
                  <option
                    v-for="prodi in prodis || []"
                    :key="prodi?.id"
                    :value="prodi?.id"
                  >
                    {{ prodi?.nama }}
                  </option>
                </select>
                <div v-if="errors?.prodi_id" class="invalid-feedback">
                  {{ errors.prodi_id[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-label11" class="form-label">Email :</label>
                <input
                  type="email"
                  v-model="form.email"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.email }"
                  id="input-label11"
                  placeholder="isikan email"
                />
                <div v-if="errors?.email" class="invalid-feedback">
                  {{ errors.email[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-label1" class="form-label">Handphone :</label>
                <input
                  type="text"
                  v-model="form.handphone"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.handphone }"
                  id="input-label1"
                  placeholder="isikan nomor handphone"
                />
                <div v-if="errors?.handphone" class="invalid-feedback">
                  {{ errors.handphone[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-label-jk" class="form-label"
                  >Jenis Kelamin :</label
                >
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.jenis_kelamin }"
                  v-model="form.jenis_kelamin"
                  id="input-label-jk"
                >
                  <option value="">Pilih Jenis Kelamin..</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
                <div v-if="errors?.jenis_kelamin" class="invalid-feedback">
                  {{ errors.jenis_kelamin[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-label1" class="form-label">Foto :</label>
                <input
                  type="file"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.foto }"
                  id="input-label1"
                  accept="image/*"
                  @change="onFileChange"
                  placeholder="Enter Client Name"
                />
                <div v-if="errors?.foto" class="invalid-feedback">
                  {{ errors.foto[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label class="form-label">Password :</label>
                <input
                  type="password"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.password }"
                  v-model="form.password"
                  id="input-label1"
                  placeholder="isikan password"
                />
                <div v-if="errors?.password" class="invalid-feedback">
                  {{ errors.password[0] }}
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
  <!--End::row-1 -->
</template>

<style scoped>
/* Add your styles here */
</style>
