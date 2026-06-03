<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import SuratComponent from "../../../components/surat_izin_penelitian/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const suratData = ref({
      id: "",
      no_surat: "",
      nomor: "",
      prodi_id: 0,
      nama: "",
      nim: "",
      prodi_mhs: "",
      kepada: "",
      semester: "",
      dari_tanggal: "",
      tanggal: "",
      jenis_kelamin: "",
      tanda_tangan_id: null as null | number,
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/surat-izin-penelitian/${id}`);
        if (response.data.status) {
          const data = response.data.data;

          suratData.value = {
            id: data.id,
            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",
            tanda_tangan_id: data.tanda_tangan_id ? Number(data.tanda_tangan_id) : null,
            nomor: data.nomor || "",
            prodi_id: data.prodi_id || 0,
            nama: data.nama || "",
            nim: data.nim || "",
            prodi_mhs: data.prodi_mhs || "",
            kepada: data.kepada || "",
            semester: data.semester || "",
            dari_tanggal: data.dari_tanggal
              ? data.dari_tanggal.slice(0, 10)
              : "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
            jenis_kelamin: data.jenis_kelamin || "",
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
        errors.value = {};
        loading.value = true;
        const response = await apiPut(
          `/surat-izin-penelitian/${form.id}`,
          form
        );
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/sip" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Surat gagal diupdate", {
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
      getSurat();
    });

    return {
      getSurat,
      suratData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <SuratComponent
    @submit="submit"
    :modelValue="suratData"
    :isEdit="true"
    :errors="errors"
    :btnLoading="loading"
  />
</template>
