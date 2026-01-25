<script lang="ts">
import { ref } from "vue";
import { defineComponent } from "vue";
import SuratKeteranganTasmaKknPplComponent from "../../../components/surat_keterangan_tasma_kkn_ppl/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratKeteranganTasmaKknPplComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    async function submit(params: any) {
      try {
        errors.value = {};
        const response = await apiPost("/sktkp", params);
        console.log("data berhasil :", response);

        if (response.success == true) {
          toast.success("Surat Berhasil Ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/sktkp" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Surat gagal Ditambahkan", {
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
  <SuratKeteranganTasmaKknPplComponent
    @submit="submit"
    :isEdit="false"
    :errors="errors"
  />
</template>
