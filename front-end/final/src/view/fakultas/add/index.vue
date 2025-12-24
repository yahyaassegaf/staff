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

    async function submit(form: any) {
      try {
        loading.value = true;
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
          toast.error("Fakultas gagal ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
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
    };
  },
});
</script>

<template>
  <FakultasComponent @submit="submit" :isEdit="false" />
</template>