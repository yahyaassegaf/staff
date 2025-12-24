<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/surat_keterangan/index.vue";
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
      nama_mhs: "",
      nim: "",
      prodi: "",
      periode_bulan: "",
      nama_staff: "",
      alasan: "",
      tanggal: "",
      prodi_id: "",
      jenis_kelamin: "",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/surat-keterangan/${id}`);
        if (response.data.status) {
          const data = response.data.data;
          suratData.value = {
            id: data.id,
            nomor: data.nomor || "",
            nama_mhs: data.nama_mahasiswa || "",
            nim: data.nim || "",
            prodi: data.prodi || "",
            periode_bulan: data.periode_bulan || "",
            nama_staff: data.nama_staff || "",
            alasan: data.alasan || "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
            prodi_id: data.prodi_id || "",
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
        const response = await apiPut(`/surat-keterangan/${form.id}`, form);
        if (response.success || response.data.status) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/sk" });
        } else {
          toast.error("Surat gagal diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat mengupdate surat", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
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
