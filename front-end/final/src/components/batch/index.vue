<script lang="ts" setup>
import { reactive, watch, ref } from "vue";

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
  nama_batch: "",
  tanggal_import: "",
};

const form = reactive({ ...defaultForm });

watch(
  () => props.modelValue,
  async (val) => {
    if (props.isEdit && val) {
      if (!val.nama_batch) {
        return;
      }
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
            <div class="card-title">{{ isEdit ? "Edit" : "Tambah" }} Batch</div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-nama-batch" class="form-label">Nama Batch :</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <input
                  type="text"
                  v-model="form.nama_batch"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_batch }"
                  id="input-nama-batch"
                  placeholder="Isikan nama batch"
                />
                <div v-if="errors?.nama_batch" class="invalid-feedback">
                  {{ errors.nama_batch[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-tanggal-import" class="form-label">Tanggal Import :</label>
                <input
                  type="date"
                  v-model="form.tanggal_import"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_import }"
                  id="input-tanggal-import"
                />
                <div v-if="errors?.tanggal_import" class="invalid-feedback">
                  {{ errors.tanggal_import[0] }}
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