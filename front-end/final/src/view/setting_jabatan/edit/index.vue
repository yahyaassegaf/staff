<script lang="ts">
import { ref, onMounted } from "vue";
import { defineComponent } from "vue";
import SettingJabatanComponent from "../../../components/setting_jabatan/index.vue";
import { apiPost, apiGet } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";
import { useRoute } from "vue-router";

export default defineComponent({
  components: {
    SettingJabatanComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    const data = ref<any>(null);
    const route = useRoute();

    async function getData() {
      try {
        const id = route.params.id;
        const response = await apiGet(`/setting-jabatan/${id}`);
        if (response.success) {
          data.value = response.data.data;
        }
      } catch (error) {
      }
    }

    onMounted(() => {
      getData();
    });

    async function submit(params: any) {
      try {
        errors.value = {};
        const id = route.params.id;

        // Prepare FormData for file upload. We use _method=PUT to fake PUT request in Laravel.
        const formData = new FormData();
        formData.append("_method", "PUT");
        formData.append("kunci_jabatan", params.kunci_jabatan);
        formData.append("nama_jabatan", params.nama_jabatan);
        if (params.nidn) {
          formData.append("nidn", params.nidn);
        }
        formData.append("nama_tanda_tangan", params.nama_tanda_tangan);
        if (params.tdd) {
          formData.append("tdd", params.tdd);
        }
        if (params.gambar) {
          formData.append("gambar", params.gambar);
        }

        const response = await apiPost(`/setting-jabatan/${id}`, formData);

        if (response.success == true || response.data?.status == true) {
          toast.success("Data Berhasil Diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/setting-jabatan" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Data gagal diupdate");
          }
        }
      } catch (error) {
      }
    }

    return {
      loading,
      submit,
      errors,
      data,
    };
  },
});
</script>
<template>
  <SettingJabatanComponent
    v-if="data"
    :data="data"
    @submit="submit"
    :isEdit="true"
    :errors="errors"
  />
</template>
