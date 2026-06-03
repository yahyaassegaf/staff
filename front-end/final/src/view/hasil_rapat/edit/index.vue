<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/hasil_rapat/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    FormComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const formData = ref<any>(null);

    async function getData() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/hasil-rapat/${id}`);
        if (response.success == true || response.data.status) {
          formData.value = response.data.data;
        }
      } catch (error) {
        console.log(error);
        toast.error("Gagal mengambil data");
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        errors.value = {};
        loading.value = true;
        const response = await apiPut(`/hasil-rapat/${form.id}`, form);
        if (response.success == true) {
          toast.success("Hasil Rapat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/hasil-rapat" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Hasil Rapat gagal diupdate", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
            });
          }
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan");
      } finally {
        loading.value = false;
      }
    }

    onMounted(() => {
      getData();
    });

    return {
      formData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <div class="container-fluid">
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Edit Hasil Rapat</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Akademik</a>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/hasil-rapat">Hasil Rapat</router-link>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
          </ol>
        </nav>
      </div>
    </div>
    <FormComponent
      v-if="formData"
      @submit="submit"
      :modelValue="formData"
      :isEdit="true"
      :errors="errors"
      :btnLoading="loading"
    />
  </div>
</template>
