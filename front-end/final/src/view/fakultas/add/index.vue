<script lang="ts">
import { defineComponent, ref } from "vue";
import FakultasComponent from "../../../components/fakultas/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    FakultasComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        const response = await apiPost("/fakultas", form);
        if (response.success == true) {
          toast.success("Fakultas berhasil ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "fakultas" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Fakultas gagal ditambahkan", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        }
      } catch (error) {
        toast.error("Terjadi kesalahan", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      } finally {
        loading.value = false;
      }
    }

    return {
      submit,
      loading,
      errors,
    };
  },
});
</script>

<template>
  <FakultasComponent @submit="submit" :isEdit="false" :errors="errors" />
</template>
