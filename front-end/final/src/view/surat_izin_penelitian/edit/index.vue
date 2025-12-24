<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/surat_izin_penelitian/index.vue";
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
    const suratData = ref({
      id: "",
      nomor: "",
      prodi_id: "",
      nama: "",
      nim: "",
      semester: "",
      dari_tanggal: "",
      tanggal: "",
      jenis_kelamin: "",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/surat-izin-penelitian/${id}`);
        if (response.data.status) {
          const data = response.data.data;
          suratData.value = {
            id: data.id,
            nomor: data.nomor || "",
            prodi_id: data.prodi_id || "",
            nama: data.nama || "",
            nim: data.nim || "",
            semester: data.semester || "",
            dari_tanggal: data.dari_tanggal
              ? data.dari_tanggal.slice(0, 10)
              : "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
            jenis_kelamin: data.jenis_kelamin || "",
          };
        }
      } catch (error) {
        console.log(error);
        toast.error("Gagal mengambil data surat");
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        loading.value = true;
        const response = await apiPut(
          `/surat-izin-penelitian/${form.id}`,
          form
        );
        if (response.success || response.data.status) {
          toast.success("Surat berhasil diupdate");
          router.push({ path: "/sip" });
        } else {
          toast.error("Surat gagal diupdate");
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat mengupdate surat");
      } finally {
        loading.value = false;
      }
    }

    onMounted(() => {
      getSurat();
    });

    return {
      suratData,
      submit,
      loading,
    };
  },
});
</script>

<template>
  <FormComponent
    v-if="suratData.id"
    @submit="submit"
    :modelValue="suratData"
    :isEdit="true"
  />
</template>
