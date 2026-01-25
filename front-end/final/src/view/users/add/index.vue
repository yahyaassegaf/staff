<script lang="ts">
import { defineComponent, ref } from "vue";
import UsersComponent from "@/components/users/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    UsersComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});

    const selectedFile = ref<File | null>(null);
    function handleFile(file: File) {
      selectedFile.value = file;
    }

    async function submit(form: any) {
      errors.value = {};
      const formData = new FormData();
      formData.append("name", form.name);
      formData.append("email", form.email);
      formData.append("level_id", form.level_id);
      formData.append("prodi_id", form.prodi_id);
      formData.append("jenis_kelamin", form.jenis_kelamin);
      formData.append("password", form.password);
      formData.append("handphone", form.handphone);

      if (selectedFile.value) {
        formData.append("foto", selectedFile.value);
      }

      const response = await apiPost("/data-users", formData);
      if (response.success == true) {
        toast.success("User berhasil ditambahkan", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
        router.push({ name: "users" });
      } else {
        if (response.error?.response?.status === 422) {
          errors.value = response.error.response.data.errors;
          toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
        } else {
          toast.error("User gagal ditambahkan", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      }
    }

    return {
      selectedFile,
      handleFile,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <UsersComponent
    @submit="submit"
    @file-change="handleFile"
    :isEdit="false"
    :errors="errors"
  />
</template>
