<script lang="ts">
import { ref } from "vue";
import { defineComponent } from "vue";
import SettingJabatanComponent from "../../../components/setting_jabatan/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    SettingJabatanComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    async function submit(params: any) {
      try {
        errors.value = {};
        
        // Prepare FormData for file upload
        const formData = new FormData();
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

        const response = await apiPost("/setting-jabatan", formData);

        if (response.success == true || response.data?.status == true) {
          toast.success("Data Berhasil Ditambahkan", {
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
            toast.error("Data gagal Ditambahkan");
          }
        }
      } catch (error) {
      }
    }

    return {
      loading,
      submit,
      errors,
    };
  },
});
</script>
<template>
  <SettingJabatanComponent
    @submit="submit"
    :isEdit="false"
    :errors="errors"
  />
</template>
