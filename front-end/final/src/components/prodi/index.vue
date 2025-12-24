<script lang="ts" setup>
import { reactive, watch } from "vue";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
});

const defaultForm = {
  id: "",
  kode: "",
  alias: "",
  nama: "",
  aktif: "T",
  jenjang: "S1",
  nidn_kepala: "",
  nama_kepala: "",
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
            <div class="card-title">{{ isEdit ? "Edit" : "Tambah" }} Prodi</div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
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

<style scoped></style>
