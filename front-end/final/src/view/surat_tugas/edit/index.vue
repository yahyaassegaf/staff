<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/surat_tugas/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    FormComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const suratData = ref({
      id: "",
      no_surat: "",
      nomor: "",
      pembimbing1: "",
      alamat_pembimbing1: "",
      tugas_pembimbing1: "",
      pembimbing2: "",
      alamat_pembimbing2: "",
      tugas_pembimbing2: "",
      nama_mhs: "",
      nim_nik: "",
      judul_skripsi: "",
      masa_penugasan: "",
      prodi_id: "",
      tanda_tangan_id: null as null | number,
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/surat-tugas/${id}`);
        if (response.data.status) {
          const data = response.data.data;
          suratData.value = {
            id: data.id,
            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",
            tanda_tangan_id: data.tanda_tangan_id ? Number(data.tanda_tangan_id) : null,
            nomor: data.nomor || "",
            pembimbing1: data.pembimbing1 || "",
            alamat_pembimbing1: data.alamat_pembimbing1 || "",
            tugas_pembimbing1: data.tugas_pembimbing1 || "",
            pembimbing2: data.pembimbing2 || "",
            alamat_pembimbing2: data.alamat_pembimbing2 || "",
            tugas_pembimbing2: data.tugas_pembimbing2 || "",
            nama_mhs: data.nama_mhs || "",
            nim_nik: data.nim_nik || "",
            judul_skripsi: data.judul_skripsi || "",
            masa_penugasan: data.masa_penugasan || "",
            prodi_id: data.prodi_id || "",
          };
        }
      } catch (error) {
        console.log(error);
        toast.error("Gagal mengambil data surat");
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        errors.value = {};
        loading.value = true;
        const response = await apiPut(`/surat-tugas/${form.id}`, form);
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/st" });
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
        toast.error("Terjadi kesalahan saat mengupdate surat", {
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
      suratData,
      submit,
      loading,
      errors,
    };
  },
});
</script>

<template>
  <FormComponent
    @submit="submit"
    :modelValue="suratData"
    :isEdit="true"
    :errors="errors"
    :btnLoading="loading"
  />
</template>
