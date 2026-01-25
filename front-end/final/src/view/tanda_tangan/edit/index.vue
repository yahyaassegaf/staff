<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/tanda_tangan/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPost } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    FormComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const formData = ref<any>(null);
    const errors = ref<any>({});

    async function getData() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/tanda-tangan/${id}`);
        if (response.success || response.data?.status) {
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
        loading.value = true;
        errors.value = {};

        const submitData = new FormData();
        submitData.append("id", form.id);
        submitData.append("nama", form.nama);
        submitData.append("tdd", form.tdd || "");
        if (form.gambar) {
          submitData.append("gambar", form.gambar);
        }
        submitData.append("_method", "PUT");

        // Use apiPost with _method: PUT for file upload support
        const response = await apiPost(`/tanda-tangan/${form.id}`, submitData);
        if (response.success || response.data?.status) {
          toast.success("Tanda Tangan berhasil diupdate");
          router.push({ path: "/tanda-tangan" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Tanda Tangan gagal diupdate");
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
      <h1 class="page-title fw-semibold fs-18 mb-0">Edit Tanda Tangan</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Master</a>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/tanda-tangan">Tanda Tangan</router-link>
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
    />
  </div>
</template>
