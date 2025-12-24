<script lang="ts" setup>
import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
import "@yaireo/tagify/dist/tagify.css";
import { onMounted, ref, reactive, watch } from "vue";
// import Pageheader from "../../../shared/components/pageheader/pageheader.vue";
// import { apiGet } from "../../services/api/request";

// onMounted(() => {
//   getLevel();
// });
// const items = ref([]);
// const loading = ref(false);

// async function getLevel() {
//   try {
//     loading.value = true;
//     const response = await apiGet("/get-level");
//     console.log(response);

//     items.value = response.data.data;
//   } catch (error) {
//     console.log(error);
//   } finally {
//     loading.value = false;
//   }
// }
// const props = defineProps({
//   modelValue: {
//     type: Object,
//     default: () => ({
//       name: "",
//       email: "",
//       handphone: "",
//       level_id: "",
//       password: "",
//       foto: "",
//       id: "",
//     }),
//   },
//   levels: {
//     type: Array,
//     default: () => [],
//   },
//   isEdit: {
//     type: Boolean,
//     default: false,
//   },
// });

 interface level {
  id:string,
  nama:string
 }

 interface prodi {
  id:string,
  nama:string
 }


// const props = defineProps({
//   modelValue: Object,
//   levels:level[],
//   prodis:Array,
//   isEdit: Boolean,
// });
const props = defineProps<{
  modelValue: any
  levels: level[]
  prodis: prodi[]
  isEdit: boolean
}>();

const defaultForm = {
  name: "",
  email: "",
  handphone: "",
  level_id: "",
  prodi_id:"",
  password: "",
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

const myFiles = ref<any[]>([]);
</script>

<template>
  <Pageheader />
  <!-- Start::row-1 -->
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">Tambah Users</div>
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
                  id="input-label"
                  placeholder="isikan username"
                />
              </div>
              <div class="col-xl-12">
                <label for="input-label11" class="form-label">Level :</label>
                <select
                  class="form-select"
                  v-model="form.level_id"
                  aria-label="Default select example"
                >
                  <option selected>Select Level..</option>
                  <option
                    v-for="level in props.levels"
                    :key="level.id"
                    :value="level.id"
                  >
                    {{ level.nama }}
                  </option>
                </select>
              </div>
              <div class="col-xl-12">
                <label for="input-label11" class="form-label">Prodi :</label>
                <select
                  class="form-select"
                  v-model="form.prodi_id"
                  aria-label="Default select example"
                >
                  <option selected>Select Prodi..</option>
                  <option
                    v-for="prodi in props.prodis"
                    :key="prodi.id"
                    :value="prodi.id"
                  >
                    {{ prodi.nama }}
                  </option>
                </select>
              </div>
              <div class="col-xl-12">
                <label for="input-label11" class="form-label">Email :</label>
                <input
                  type="email"
                  v-model="form.email"
                  class="form-control"
                  id="input-label11"
                  placeholder="isikan email"
                />
              </div>
              <div class="col-xl-12">
                <label for="input-label1" class="form-label">Handphone :</label>
                <input
                  type="text"
                  v-model="form.handphone"
                  class="form-control"
                  id="input-label1"
                  placeholder="isikan nomor handphone"
                />
              </div>
              <div class="col-xl-12">
                <label for="input-label1" class="form-label">Foto :</label>
                <input
                  type="file"
                  class="form-control"
                  id="input-label1"
                  accept="image/*"
                  @change="onFileChange"
                  placeholder="Enter Client Name"
                />
              </div>
              <div class="col-xl-12">
                <label class="form-label">Password :</label>
                <input
                  type="password"
                  class="form-control"
                  v-model="form.password"
                  id="input-label1"
                  placeholder="isikan password"
                />
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
