<script lang="ts">
import { ref, onMounted } from "vue";
import { defineComponent } from "vue";
import Sk6Component from "../../../components/sk_6/index.vue";
import { apiPut, apiGet } from "../../../services/api/request";
import { toast } from "vue3-toastify";
import router from "../../../router";
import { useRoute } from "vue-router";

export default defineComponent({
  components: {
    Sk6Component,
  },
  setup() {
    const loading = ref(false);
    const errors = ref<any>({});
    const data = ref<any>(null);
    const route = useRoute();

    async function getData() {
      try {
        const id = route.params.id;
        const response = await apiGet(`/sk6/${id}`);
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
        const response = await apiPut(`/sk6/${id}`, params);

        if (response.success == true) {
          toast.success("5 Surat Keterangan Berhasil Diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/sk6" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Gagal mengupdate SK 6");
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
  <Sk6Component
    v-if="data"
    :data="data"
    @submit="submit"
    :isEdit="true"
    :errors="errors"
  />
</template>
