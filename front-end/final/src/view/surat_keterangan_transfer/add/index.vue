<script lang="ts">
import { ref, defineComponent } from "vue";
import FormComponent from "../../../components/surat_keterangan_transfer/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    FormComponent,
  },
  setup() {
    const loading = ref(false);
    async function submit(params: any) {
      try {
        loading.value = true;
        const response = await apiPost("/surat-keterangan-transfer", params);

        if (response.success || response.data.status) {
          toast.success("Surat Berhasil Ditambahkan");
          router.push({ path: "/skt" });
        } else {
          toast.error("Surat gagal Ditambahkan");
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat menambahkan surat");
      } finally {
        loading.value = false;
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
  <FormComponent @submit="submit" :isEdit="false" />
</template>
