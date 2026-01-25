<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import ProdiComponent from "../../../components/prodi/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    ProdiComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const prodiData = ref({
      id: "",
      kode: "",
      alias: "",
      nama: "",
      aktif: "T",
      jenjang: "S1",
      nidn_kepala: "",
      nama_kepala: "",
      tanda_tangan: null as {id: number, nama: string} | null,
    });

    async function getProdi() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/prodi/${id}`);
        if (response.success|| response.data?.status) {
          const prodi = response.data.data;
          console.log('Prodi data from API:', prodi);
          
          prodiData.value = {
            id: prodi.id,
            kode: prodi.kode || "",
            alias: prodi.alias || "",
            nama: prodi.nama || "",
            aktif: prodi.aktif || "T",
            jenjang: prodi.jenjang || "S1",
            nidn_kepala: prodi.nidn_kepala || "",
            nama_kepala: prodi.nama_kepala || "",
            tanda_tangan: prodi.tanda_tangan_id ? {
              id: prodi.tanda_tangan_id,
              nama: prodi.tanda_tangan || ''
            } : null,
          };
          
          console.log('Prodi data for form:', prodiData.value);
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
        errors.value = {};
        const response = await apiPut(`/prodi/${form.id}`, form);
        if (response.success == true) {
          toast.success("Prodi berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "prodi" });
        } else {
          if ((response.error as any)?.response?.data?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Prodi gagal diupdate", {
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
      getProdi();
    });

    return {
      getProdi,
      prodiData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <ProdiComponent
    @submit="submit"
    :modelValue="prodiData"
    :isEdit="true"
    :errors="errors"
  />
</template>
