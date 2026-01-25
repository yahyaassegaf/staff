<script lang="ts">
import { defineComponent } from "vue";
import { ref } from "vue";
import ProdiComponent from "../../../components/prodi/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    ProdiComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        const response = await apiPost("/prodi", form);
        if (response.success == true) {
          toast.success("Prodi berhasil ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "prodi" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Prodi gagal ditambahkan", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        }
      } catch (error) {
        console.log(error);
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
  <ProdiComponent @submit="submit" :isEdit="false" :errors="errors" />
</template>
