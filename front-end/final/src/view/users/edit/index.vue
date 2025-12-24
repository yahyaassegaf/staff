<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import UsersComponent from "../../../components/users/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPost, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    UsersComponent,
  },
  setup() {
    const route = useRoute();
    const items = ref([]);
    const loading = ref(false);
    const userData = ref({
      id: "",
      name: "",
      email: "",
      handphone: "",
      level_id: "",
      password: "",
    });

    async function getLevel() {
      try {
        loading.value = true;
        const response = await apiGet("/get-level");
        items.value = response.data.data;
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }

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
      const formData = new FormData();
      formData.append("id", form.id);
      formData.append("name", form.name);
      formData.append("email", form.email);
      formData.append("level_id", form.level_id);
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
        toast.error("User gagal diupdate", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
    }

    onMounted(() => {
      getLevel();
      getUser();
    });

    return {
      getLevel,
      getUser,
      items,
      userData,
      selectedFile,
      handleFile,
      submit,
      loading,
    };
  },
});
</script>
<template>
  <UsersComponent
    @submit="submit"
    @file-change="handleFile"
    :levels="items"
    :modelValue="userData"
    :isEdit="true"
  />
</template>
