<script lang="ts">
import { ref, defineComponent } from "vue";
import FormComponent from "../../../components/tanda_tangan/index.vue";
import { apiPost } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";

export default defineComponent({
  components: {
    FormComponent,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    async function submit(params: any) {
      try {
        loading.value = true;
        errors.value = {};

        const formData = new FormData();
        formData.append("nama", params.nama);
        formData.append("tdd", params.tdd);
        if (params.gambar) {
          formData.append("gambar", params.gambar);
        }

        const response = await apiPost("/tanda-tangan", formData);

        if (response.success || response.data?.status) {
          toast.success("Tanda Tangan Berhasil Ditambahkan");
          router.push({ path: "/tanda-tangan" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
          } else {
            toast.error("Tanda Tangan gagal Ditambahkan");
          }
        }
      } catch (error) {
        toast.error("Terjadi kesalahan");
      } finally {
        loading.value = false;
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
  <div class="container-fluid">
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Tambah Tanda Tangan</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Master</a>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/tanda-tangan">Tanda Tangan</router-link>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Tambah</li>
          </ol>
        </nav>
      </div>
    </div>
    <FormComponent @submit="submit" :isEdit="false" :errors="errors" />
  </div>
</template>
