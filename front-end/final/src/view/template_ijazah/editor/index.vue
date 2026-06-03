<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import TemplateEditorComponent from "../../../components/template_ijazah/editor.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    TemplateEditorComponent,
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
      fields_positions: {} as any,
    });

    async function getTemplate() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/template-ijazah/${id}`);
        if (response.success || response.data?.status) {
          const template = response.data.data;

          // Parse fields_positions if it exists as JSON string
          let fieldsPositions = {};
          if (template.fields_positions) {
            try {
              fieldsPositions = typeof template.fields_positions === 'string' 
                ? JSON.parse(template.fields_positions) 
                : template.fields_positions;
            } catch (e) {
            }
          }

          templateData.value = {
            id: template.id,
            prodi_id: template.prodi_id ? Number(template.prodi_id) : null,
            jenjang: template.jenjang || "",
            nama_template: template.nama_template || "",
            file_background: template.file_background || "",
            ukuran_kertas: template.ukuran_kertas || "A4",
            orientasi: template.orientasi || "portrait",
            is_active: template.is_active || "aktif",
            fields_positions: fieldsPositions,
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
        
        // Convert fields_positions to JSON string
        const formData = {
          ...form,
          fields_positions: JSON.stringify(form.fields_positions || {}),
        };

        const response = await apiPut(`/template-ijazah/${form.id}`, formData);
        if (response.success == true) {
          toast.success("Template ijazah berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "template-ijazah" });
        } else {
          if ((response.error as any)?.response?.data?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Template ijazah gagal diupdate", {
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
  <TemplateEditorComponent
    @submit="submit"
    :modelValue="templateData"
    :isEdit="true"
    :errors="errors"
  />
</template>
