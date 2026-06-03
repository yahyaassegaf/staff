<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import MahasiswaComponent from "../../../components/mahasiswa/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    MahasiswaComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const mahasiswaData = ref({
      id: "",
      nama: "",
      nim: "",
      nik: "",
      tgl_lahir: "",
      nilai_akreditasi: "",
      nomor_sk_ban_pt: "",
      nomor_ijazah_nasional: "",
      tanggal_sk_yudisium: "",
      tanggal_penerbitan: "",
      prodi_id: null as number | null,
      status: "belum",
    });

    async function getMahasiswa() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/mahasiswa/${id}`);
        if (response.success || response.data?.status) {
          const data = response.data.data;

          mahasiswaData.value = {
            id: data.id,
            nama: data.nama || "",
            nim: data.nim || "",
            nik: data.nik || "",
            tgl_lahir: data.tgl_lahir || "",
            nilai_akreditasi: data.nilai_akreditasi || "",
            nomor_sk_ban_pt: data.nomor_sk_ban_pt || "",
            nomor_ijazah_nasional: data.nomor_ijazah_nasional || "",
            tanggal_sk_yudisium: data.tanggal_sk_yudisium || "",
            tanggal_penerbitan: data.tanggal_penerbitan || "",
            prodi_id: data.prodi_id ? Number(data.prodi_id) : null,
            status: data.status || "belum",
          };

        }
      } catch (error) {
        toast.error("Gagal mengambil data mahasiswa", {
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

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        
        
        const { ...formData } = form;
        
        // Convert prodi_id to integer or null
        if (formData.prodi_id && typeof formData.prodi_id === 'object') {
          formData.prodi_id = formData.prodi_id.id || null;
        } else if (formData.prodi_id) {
          formData.prodi_id = Number(formData.prodi_id);
        } else {
          formData.prodi_id = null;
        }
        
        
        const response = await apiPut(`/mahasiswa/${formData.id}`, formData);
        
        if (response.success == true || response.data?.status == true) {
          toast.success("Mahasiswa berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "mahasiswa" });
        } else {
          const errorData = (response.error as any)?.response?.data;
          if (errorData?.status === 422 || errorData?.errors) {
            errors.value = errorData.errors || errorData;
            
          } else {
            toast.error(response.message || "Mahasiswa gagal diupdate", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        }
      } catch (error: any) {
        
        if (error.response) {
          const errorData = error.response.data;
          
          if (error.response.status === 422 && errorData.errors) {
            errors.value = errorData.errors;
            
          } else {
            toast.error(errorData.message || "Terjadi kesalahan saat mengupdate data", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        } else {
          toast.error("Terjadi kesalahan koneksi", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } finally {
        loading.value = false;
      }
    }

    onMounted(() => {
      getMahasiswa();
    });

    return {
      getMahasiswa,
      mahasiswaData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <MahasiswaComponent
    @submit="submit"
    :modelValue="mahasiswaData"
    :isEdit="true"
    :errors="errors"
  />
</template>
