<script lang="ts">
import { ref } from "vue";
import { defineComponent } from "vue";
import SuratKeteranganPplComponent from "../../../components/surat_keterangan_ppl/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratKeteranganPplComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    async function submit(params: any) {
      try {
        errors.value = {};
        const response = await apiPost("/skp", params);
        console.log("data berhasil :", response);

        if (response.success == true) {
          toast.success("Surat Berhasil Ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
            isLoading: false,
          });
          router.push({ path: "/skp" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda", {
              isLoading: false,
            });
          } else {
            toast.error("Surat gagal Ditambahkan", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
              isLoading: false,
            });
          }
        }
      } catch (error) {
        console.log(error);
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
  <SuratKeteranganPplComponent
    @submit="submit"
    :isEdit="false"
    :errors="errors"
  />
</template>
