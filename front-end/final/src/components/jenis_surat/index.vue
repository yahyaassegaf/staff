<script lang="ts" setup>
import { reactive, watch, ref } from "vue";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
  errors: { type: Object, default: () => ({}) },
});

const defaultForm = {
  id: "",
  nama: "",
  alias: "",
  format_surat: "",
};

const form = reactive({ ...defaultForm });

watch(
  () => props.modelValue,
  async (val) => {
    if (props.isEdit && val) {
      if (!val.alias) return;
      Object.assign(form, defaultForm);
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
            <div class="card-title">{{ isEdit ? "Edit" : "Tambah" }} Jenis Surat</div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label class="form-label">Nama :</label>
                <input type="text" v-model="form.nama" class="form-control"
                  :class="{ 'is-invalid': errors?.nama }" placeholder="Isikan nama jenis surat" />
                <div v-if="errors?.nama" class="invalid-feedback">{{ errors.nama[0] }}</div>
              </div>
              <div class="col-xl-12">
                <label class="form-label">Alias :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <input type="text" v-model="form.alias" class="form-control"
                  :class="{ 'is-invalid': errors?.alias }" placeholder="Isikan alias jenis surat" />
                <div v-if="errors?.alias" class="invalid-feedback">{{ errors.alias[0] }}</div>
              </div>
              <div class="col-xl-12">
                <label class="form-label">Format Surat :</label>
                <input type="text" v-model="form.format_surat" class="form-control"
                  :class="{ 'is-invalid': errors?.format_surat }" placeholder="Isikan format surat" />
                <div v-if="errors?.format_surat" class="invalid-feedback">{{ errors.format_surat[0] }}</div>
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
