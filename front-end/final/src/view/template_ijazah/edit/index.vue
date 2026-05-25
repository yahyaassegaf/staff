<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import TemplateIjazahComponent from "../../../components/template_ijazah/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    TemplateIjazahComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const templateData = ref({
      id: "",
      prodi_id: null as number | null,
      jenjang: "",
      nama_template: "",
      file_background: "",
      ukuran_kertas: "A4",
      orientasi: "portrait",
      is_active: "aktif",
      fields_positions: null as string | null,
    });

    async function getTemplate() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/template-ijazah/${id}`);
        if (response.success || response.data?.status) {
          const template = response.data.data;

          templateData.value = {
            id: template.id,
            prodi_id: template.prodi_id ? Number(template.prodi_id) : null,
            jenjang: template.jenjang || "",
            nama_template: template.nama_template || "",
            file_background: template.file_background || "",
            ukuran_kertas: template.ukuran_kertas || "A4",
            orientasi: template.orientasi || "portrait",
            is_active: template.is_active || "aktif",
            fields_positions: template.fields_positions || null,
            nama_prodi: template.nama_prodi || "",
          };

        }
      } catch (error) {
        toast.error("Gagal mengambil data template", {
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

        // Prepare data for API using FormData for file support
        const formData = new FormData();
        formData.append('_method', 'PUT'); // CRITICAL for Laravel to handle FormData in PUT
        formData.append('id', form.id);
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

        if (form.fields_positions) {
          formData.append('fields_positions', form.fields_positions);
        }

        // Use apiPost instead of apiPut because we're using _method: 'PUT' with FormData
        const response = await apiPost(`/template-ijazah/${form.id}`, formData);

        if (response.success == true || response.data?.status == true) {
          toast.success("Template ijazah berhasil diupdate", {
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
            toast.error(response.message || "Template ijazah gagal diupdate", {
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
      getTemplate();
    });

    return {
      getTemplate,
      templateData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <TemplateIjazahComponent
    @submit="submit"
    :modelValue="templateData"
    :isEdit="true"
    :errors="errors"
  />
</template>
