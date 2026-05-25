<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import JenisSuratComponent from "../../../components/jenis_surat/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: { JenisSuratComponent },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const jenisSuratData = ref({
      id: "",
      nama: "",
      alias: "",
      format_surat: "",
    });

    async function getJenisSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/jenis-surat/${id}`);
        if (response.success || response.data?.status) {
          const data = response.data.data;
          jenisSuratData.value = {
            id: data.id,
            nama: data.nama || "",
            alias: data.alias || "",
            format_surat: data.format_surat || "",
          };
        }
      } catch (error) {
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        const response = await apiPut(`/jenis-surat/${form.id}`, form);
        if (response.success == true || response.data?.status) {
          toast.success("Jenis surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "jenis-surat" });
        } else {
          if (
            (response.error as any)?.response?.data?.status === 422 ||
            (response.error as any)?.response?.status === 422
          ) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          } else {
            toast.error("Jenis surat gagal diupdate", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        }
      } catch (error) {
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

    onMounted(() => {
      getJenisSurat();
    });
    return { getJenisSurat, jenisSuratData, submit, loading, errors };
  },
});
</script>
<template>
  <JenisSuratComponent
    @submit="submit"
    :modelValue="jenisSuratData"
    :isEdit="true"
    :errors="errors"
  />
</template>
