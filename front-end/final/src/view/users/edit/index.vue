<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import UsersComponent from "../../../components/users/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    UsersComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const userData = ref({
      id: "",
      name: "",
      email: "",
      handphone: "",
      level_id: "",
      prodi_id: "",
      jenis_kelamin: "",
      password: "",
    });

    async function getUser() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/data-users/${id}`);
        if (response.data.status) {
          const user = response.data.user;
          userData.value = {
            id: user.id,
            name: user.name,
            email: user.email,
            handphone: user.phone,
            level_id: user.level_id,
            prodi_id: user.prodi_id,
            jenis_kelamin: user.jenis_kelamin,
            password: "",
          };
        }
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }

    const selectedFile = ref<File | null>(null);
    function handleFile(file: File) {
      selectedFile.value = file;
    }

    async function submit(form: any) {
      errors.value = {};
      const formData = new FormData();
      formData.append("id", form.id);
      formData.append("name", form.name);
      formData.append("email", form.email);
      formData.append("level_id", form.level_id);
      formData.append("prodi_id", form.prodi_id);
      formData.append("jenis_kelamin", form.jenis_kelamin);
      formData.append("handphone", form.handphone);

      if (form.password) {
        formData.append("password", form.password);
      }

      if (selectedFile.value) {
        formData.append("foto", selectedFile.value);
      }

      formData.append("_method", "PUT");

      const response = await apiPut(`/data-users/${form.id}`, formData);
      if (response.success == true) {
        toast.success("User berhasil diupdate", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
        router.push({ name: "users" });
      } else {
        if ((response.error as any)?.response?.status === 422) {
          errors.value = (response.error as any).response.data.errors;
          
        } else {
          toast.error("User gagal diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      }
    }

    onMounted(() => {
      getUser();
    });

    return {
      getUser,
      userData,
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
    :modelValue="userData"
    :isEdit="true"
    :errors="errors"
  />
</template>
