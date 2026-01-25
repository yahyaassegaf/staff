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
      nomor: "",
      nama_dosen: "",
      alamat_dosen: "",
      tugas_dosen: "",
      tugasnya: "",
      nama_mhs: "",
      nim_nik: "",
      fakultas_prodi: "",
      judul_skripsi: "",
      masa_penugasan: "",
      tanggal: "",
      prodi_id: "",
      jenis_kelamin: "",
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
            nomor: data.nomor || "",
            nama_dosen: data.nama_dosen || "",
            alamat_dosen: data.alamat_dosen || "",
            tugas_dosen: data.tugas_dosen || "",
            tugasnya: data.tugasnya || "",
            nama_mhs: data.nama_mhs || "",
            nim_nik: data.nim_nik || "",
            fakultas_prodi: data.fakultas_prodi || "",
            judul_skripsi: data.judul_skripsi || "",
            masa_penugasan: data.masa_penugasan || "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
            prodi_id: data.prodi_id || "",
            jenis_kelamin: data.jenis_kelamin || "",
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
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
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
  />
</template>
