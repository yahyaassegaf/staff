<script lang="ts">
import { defineComponent } from "vue";
import { ref } from "vue";
import MahasiswaComponent from "../../../components/mahasiswa/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    MahasiswaComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        
        
        // Convert prodi_id if it's an object
        const { ...formData } = form;
        
        if (formData.prodi_id && typeof formData.prodi_id === 'object') {
          formData.prodi_id = formData.prodi_id.id || null;
        } else if (formData.prodi_id) {
          formData.prodi_id = Number(formData.prodi_id);
        }
        
        
        const response = await apiPost("/mahasiswa", formData);
        
        if (response.success == true || response.data?.status == true) {
          toast.success("Mahasiswa berhasil ditambahkan", {
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
            toast.error(response.message || "Mahasiswa gagal ditambahkan", {
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
            toast.error(errorData.message || "Terjadi kesalahan saat menambahkan data", {
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

    return {
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <MahasiswaComponent @submit="submit" :isEdit="false" :errors="errors" />
</template>
