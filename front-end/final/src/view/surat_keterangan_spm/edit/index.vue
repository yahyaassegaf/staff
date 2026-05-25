<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import SuratComponent from "../../../components/surat_keterangan_spm/index.vue";
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
      nomor_surat: "",
      prodi_id: 0,
      nama_lengkap: "",
      nim: "",
      tempat_lahir: "",
      tanggal_lahir: "",
      prodi_mhs: "",
      alamat: "",
      nama_ortu: "",
      tempat_tugas: "",
      alamat_tugas: "",
      tahun: "",
      semester: "",
      tanggal: "",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/spm/${id}`);
        if (response.data.status) {
          const data = response.data.data;

          suratData.value = {
            id: data.id,
            nomor_surat: data.nomor_surat || "",
            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",
            prodi_id: data.prodi_id || 0,
            nama_lengkap: data.nama_lengkap || "",
            nim: data.nim || "",
            tempat_lahir: data.tempat_lahir || "",
            tanggal_lahir: data.tanggal_lahir ? data.tanggal_lahir.slice(0, 10) : "",
            prodi_mhs: data.prodi_mhs || "",
            alamat: data.alamat || "",
            nama_ortu: data.nama_ortu || "",
            tempat_tugas: data.tempat_tugas || "",
            alamat_tugas: data.alamat_tugas || "",
            tahun: data.tahun || "",
            semester: data.semester || "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
          };
        }
      } catch (error) {
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        errors.value = {};
        loading.value = true;
        const response = await apiPut(`/spm/${form.id}`, form);
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/spm" });
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
  />
</template>
