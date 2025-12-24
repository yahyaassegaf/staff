<script lang="ts">
import { defineComponent, ref } from "vue";
import SuratKeteranganAdministrasiKeuanganComponent from "../../../components/surat_keterangan_administrasi_keuangan/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratKeteranganAdministrasiKeuanganComponent,
  },
  setup() {
    const loading = ref(false);
    async function submit(params: any) {
      try {
        const response = await apiPost("/skak", params);
        console.log("data berhasil :", response);

        if (response.success == true) {
          toast.success("Surat Berhasil Ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/skak" });
        } else {
          toast.error("Surat gagal Ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        console.log(error);
      }
    }

    return {
      loading,
      submit,
    };
  },
});
</script>
<template>
  <SuratKeteranganAdministrasiKeuanganComponent
    @submit="submit"
    :isEdit="false"
  />
</template>
