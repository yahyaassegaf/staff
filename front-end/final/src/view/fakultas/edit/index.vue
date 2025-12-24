<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FakultasComponent from "../../../components/fakultas/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    FakultasComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const fakultasData = ref({
      id: "",
      kode: "",
      alias: "",
      nama: "",
      nama_fakultas: "",
      kode_fakultas: "",
      dekan: "",
      jenjang: "S1",
      nidn_kepala: "",
      nama_kepala: "",
    });

    async function getFakultas() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/fakultas/${id}`);
        if (response.success) {
          const fakultas = response.data.data;
          fakultasData.value = {
            id: fakultas.id,
            kode: fakultas.kode || "",
            alias: fakultas.alias || "",
            nama: fakultas.nama || "",
            nama_fakultas: fakultas.nama_fakultas || "",
            kode_fakultas: fakultas.kode_fakultas || "",
            dekan: fakultas.dekan || "",
            jenjang: fakultas.jenjang || "S1",
            nidn_kepala: fakultas.nidn_kepala || "",
            nama_kepala: fakultas.nama_kepala || "",
          };
        }
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        loading.value = true;
        const response = await apiPut(`/fakultas/${form.id}`, form);
        if (response.success == true) {
          toast.success("Fakultas berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "fakultas" });
        } else {
          toast.error("Fakultas gagal diupdate", {
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

    onMounted(() => {
      getFakultas();
    });

    return {
      getFakultas,
      submit,
      fakultasData,
      loading,
    };
  },
});
</script>

<template>
  <FakultasComponent @submit="submit" :modelValue="fakultasData" :isEdit="true" />
</template>