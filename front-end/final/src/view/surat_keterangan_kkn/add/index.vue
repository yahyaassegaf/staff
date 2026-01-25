<script lang="ts">
import { ref } from "vue";
import { defineComponent } from "vue";
import SuratKeteranganKknComponent from "../../../components/surat_keterangan_kkn/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratKeteranganKknComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    async function submit(params: any) {
      try {
        errors.value = {};
        const response = await apiPost("/skk", params);
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
          router.push({ path: "/skk" });
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
  <SuratKeteranganKknComponent
    @submit="submit"
    :isEdit="false"
    :errors="errors"
  />
</template>
