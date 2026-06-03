<script lang="ts">
import { ref } from "vue";
import { defineComponent } from "vue";
import Sk6Component from "../../../components/sk_6/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    Sk6Component,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    async function submit(params: any) {
      try {
        loading.value = true;
        errors.value = {};
        const response = await apiPost("/sk6", params);

        if (response.success == true) {
          toast.success("5 Surat Keterangan Berhasil Ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/sk6" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Gagal menambahkan SK 6");
          }
        }
      } catch (error) {
      } finally {
        loading.value = false;
      }
    }

    return {
      loading,
      submit,
      errors,
    };
  },
});
</script>
<template>
  <Sk6Component
    @submit="submit"
    :isEdit="false"
    :errors="errors"
    :btnLoading="loading"
  />
</template>
