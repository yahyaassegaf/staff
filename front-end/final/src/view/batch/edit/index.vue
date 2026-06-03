<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import BatchComponent from "../../../components/batch/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    BatchComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const batchData = ref({
      id: "",
      nama_batch: "",
      tanggal_import: "",
    });

    async function getBatch() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/batch/${id}`);
        if (response.success || response.data?.status) {
          const batch = response.data.data;

          batchData.value = {
            id: batch.id,
            nama_batch: batch.nama_batch || "",
            tanggal_import: batch.tanggal_import || "",
          };

        }
      } catch (error) {
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        loading.value = true;
        errors.value = {};
        const response = await apiPut(`/batch/${form.id}`, form);
        if (response.success == true || response.data?.status) {
          toast.success("Batch berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ name: "batch" });
        } else {
          if ((response.error as any)?.response?.data?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Batch gagal diupdate", {
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
      getBatch();
    });

    return {
      getBatch,
      batchData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <BatchComponent
    @submit="submit"
    :modelValue="batchData"
    :isEdit="true"
    :errors="errors"
  />
</template>