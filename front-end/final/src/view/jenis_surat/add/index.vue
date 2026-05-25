<script lang="ts">
import { defineComponent } from "vue";
import JenisSuratComponent from "../../../components/jenis_surat/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: { JenisSuratComponent },
  setup() {
    async function submit(form: any) {
      try {
        const response = await apiPost("/jenis-surat", form);
        if (response.success || response.data?.status) {
          toast.success("Jenis surat berhasil ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "jenis-surat" });
        } else {
          toast.error("Jenis surat gagal ditambahkan", {
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
    return { submit };
  },
});
</script>
<template>
  <JenisSuratComponent @submit="submit" :isEdit="false" />
</template>
