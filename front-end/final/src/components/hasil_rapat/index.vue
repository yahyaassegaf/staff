<script lang="ts" setup>
import { reactive, ref, watch, onMounted } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import { apiGet } from "../../services/api/request";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
});

const defaultForm = {
  id: "",
  nomor_surat: "",
  prodi_id: 0,
  agenda: "",
  tanggal: "",
  waktu: "",
  tempat: "",
  pembahasan: "",
  anggota_ids: [] as number[],
};

const form = reactive({ ...defaultForm });

const userOptions = ref<any[]>([]);
const selectedAnggota = ref<any[]>([]);
const loadingUsers = ref(false);

const listProdi = ref<any[]>([]);

async function getProdi() {
  try {
    const response = await apiGet(`/get-prodi`);
    if (response.success) {
      const data = response.data?.data;
      listProdi.value = Array.isArray(data) ? data : [data];
      if (listProdi.value.length === 1 && !props.isEdit) {
        form.prodi_id = Number(listProdi.value[0].id);
      }
    }
  } catch (error) {
    console.log(error);
  }
}

async function getUsers() {
  try {
    loadingUsers.value = true;
    const response = await apiGet("/data-users");
    if (response.success) {
      userOptions.value = response.data.data.data || response.data.data || [];
    }
  } catch (error) {
    console.log(error);
  } finally {
    loadingUsers.value = false;
  }
}

onMounted(() => {
  getProdi();
  getUsers();
});

watch(selectedAnggota, (val) => {
  form.anggota_ids = val.map((u: any) => u.id);
});

watch(
  () => props.modelValue,
  (val) => {
    if (!props.isEdit || !val) return;

    Object.assign(form, defaultForm);

    form.id = val.id ?? "";
    form.nomor_surat = val.nomor_surat ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.agenda = val.agenda ?? "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";
    form.waktu = val.waktu ?? "";
    form.tempat = val.tempat ?? "";
    form.pembahasan = val.pembahasan ?? "";

    if (val.anggota) {
      selectedAnggota.value = val.anggota.map((a: any) => ({
        id: a.user_id,
        name: a.user ? a.user.name : "",
      }));
    }
  },
  { immediate: true },
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
              {{ isEdit ? "Edit" : "Tambah" }} Hasil Rapat
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-6">
                <label class="form-label">Program Studi Unit:</label>
                <select class="form-select" v-model="form.prodi_id" required>
                  <option value="0" disabled>Pilih Prodi</option>
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
                <label class="form-label">Agenda :</label>
                <input
                  type="text"
                  v-model="form.agenda"
                  class="form-control"
                  placeholder="Isikan Nama Agenda Rapat"
                  required
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tanggal :</label>
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  required
                />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Waktu :</label>
                <input type="time" v-model="form.waktu" class="form-control" />
              </div>

              <div class="col-xl-4">
                <label class="form-label">Tempat :</label>
                <input
                  type="text"
                  v-model="form.tempat"
                  class="form-control"
                  placeholder="Contoh: Ruang Rapat Dekanat"
                />
              </div>

              <div class="col-xl-12">
                <label class="form-label">Anggota Rapat :</label>
                <Multiselect
                  v-model="selectedAnggota"
                  :options="userOptions"
                  :multiple="true"
                  :close-on-select="false"
                  :clear-on-select="false"
                  :preserve-search="true"
                  placeholder="Pilih Anggota Rapat"
                  label="name"
                  track-by="id"
                  :loading="loadingUsers"
                >
                </Multiselect>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Hasil Pembahasan :</label>
                <QuillEditor
                  v-model:content="form.pembahasan"
                  contentType="html"
                  theme="snow"
                  placeholder="Isikan hasil pembahasan rapat..."
                  style="min-height: 200px"
                  :toolbar="[
                    [{ font: [] }],
                    [{ size: ['small', false, 'large', 'huge'] }],
                    [{ header: [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ color: [] }, { background: [] }],
                    [{ script: 'sub' }, { script: 'super' }],
                    ['blockquote', 'code-block'],
                    [
                      { list: 'ordered' },
                      { list: 'bullet' },
                      { list: 'check' },
                    ],
                    [{ indent: '-1' }, { indent: '+1' }],
                    [{ direction: 'rtl' }],
                    [{ align: [] }],
                    ['link', 'image', 'video', 'formula'],
                    ['clean'],
                  ]"
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

<style scoped>
:deep(.ql-toolbar) {
  position: relative;
  z-index: 1;
}

:deep(.ql-container) {
  position: relative;
  z-index: 1;
  min-height: 200px;
  max-height: 400px;
  overflow-y: auto;
}

:deep(.ql-editor) {
  min-height: 200px;
}

.card-footer {
  position: relative;
  z-index: 10;
}
</style>
