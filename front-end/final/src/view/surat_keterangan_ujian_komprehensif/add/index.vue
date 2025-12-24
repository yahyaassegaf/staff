<script lang="ts">
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import SuratKeteranganKomprehensifComponent from "../../../components/surat_keterangan_ujian_komprehensif/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratKeteranganKomprehensifComponent,
  },
  setup() {
    const loading = ref(false);
    async function submit(params: any) {
      try {
        const response = await apiPost("/skukd", params);
        console.log("data berhasil :", response);

        if (response.success == true) {
          toast.success("Surat Berhasil Ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/skukd" });
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
  <SuratKeteranganKomprehensifComponent @submit="submit" :isEdit="false" />
</template>
