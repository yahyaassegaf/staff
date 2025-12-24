<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref, watch } from "vue";
import UsersComponent from "@/components/users/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    UsersComponent,
  },
  setup() {
    const items = ref([]);
    const loading = ref(false);
    const itemsProdi = ref([]);

    async function getProdi() {
      try {
        loading.value = true;
        const response = await apiGet('/get-prodi');
        itemsProdi.value = response.data.data;
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }
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
    const selectedFile = ref<File | null>(null);
    function handleFile(file: File) {
      selectedFile.value = file;
    }

    async function submit(form: any) {
      const formData = new FormData();
      formData.append("name", form.name);
      formData.append("email", form.email);
      formData.append("level_id", form.level_id);
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
        toast.error("User gagal ditambahkan", {
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
      getProdi();
    });

    return {
      getLevel,
      items,
      selectedFile,
      handleFile,
      submit,
      loading,
      itemsProdi,
    };
  },
});
</script>
<template>
  <UsersComponent
    @submit="submit"
    @file-change="handleFile"
    :levels="items"
    :prodis="itemsProdi"
    :isEdit="false"
  />
</template>
