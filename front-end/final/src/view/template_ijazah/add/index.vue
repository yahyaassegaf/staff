<script lang="ts">
import { defineComponent } from "vue";
import { ref } from "vue";
import TemplateIjazahComponent from "../../../components/template_ijazah/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    TemplateIjazahComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        
        // Prepare data for API using FormData for file support
        const formData = new FormData();
        formData.append('nama_template', form.nama_template);
        formData.append('jenjang', form.jenjang || '');
        formData.append('ukuran_kertas', form.ukuran_kertas);
        formData.append('orientasi', form.orientasi);
        formData.append('is_active', form.is_active);
        
        // Extract prodi_id correctly (now already an ID from the component)
        if (form.prodi_id !== null && form.prodi_id !== undefined) {
          formData.append('prodi_id', String(form.prodi_id));
        }
        
        if (form.selectedFile) {
          formData.append('file_background', form.selectedFile);
        }

        const response = await apiPost("/template-ijazah", formData);
        
        if (response.success == true || response.data?.status == true) {
          toast.success("Template ijazah berhasil ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "template-ijazah" });
        } else {
          // Check for validation errors
          const errorData = (response.error as any)?.response?.data;
          if (errorData?.status === 422 || errorData?.errors) {
            errors.value = errorData.errors || errorData;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          } else {
            toast.error(response.message || "Template ijazah gagal ditambahkan", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        }
      } catch (error: any) {
        
        // Extract error details
        if (error.response) {
          const errorData = error.response.data;
          
          if (error.response.status === 422 && errorData.errors) {
            errors.value = errorData.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
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
  <TemplateIjazahComponent @submit="submit" :isEdit="false" :errors="errors" />
</template>
