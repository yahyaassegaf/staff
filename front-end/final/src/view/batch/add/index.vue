<script lang="ts">
import { defineComponent } from "vue";
import BatchComponent from "../../../components/batch/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    BatchComponent,
  },
  setup() {
    async function submit(form: any) {
      try {
        const response = await apiPost("/batch", form);
        if (response.success || response.data?.status) {
          toast.success("Batch berhasil ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "batch" });
        } else {
          toast.error("Batch gagal ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        toast.error("Terjadi kesalahan", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
    }

    return {
      submit,
    };
  },
});
</script>
<template>
  <BatchComponent @submit="submit" :isEdit="false" />
</template>