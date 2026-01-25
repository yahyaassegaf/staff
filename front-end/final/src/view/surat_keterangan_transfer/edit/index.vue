<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/surat_keterangan_transfer/index.vue";
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
    const suratData = ref({
      id: "",
      nomor: "",
      prodi_id: "",
      th_akademik_id: null as null | number,
      nama: "",
      tanggal_lahir: "",
      nim: "",
      jurusan_prodi: "",
      semester: "",
      tahun_akademik: "",
      tanggal: "",
      jenis_kelamin: "",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/surat-keterangan-transfer/${id}`);
        if (response.data.status) {
          const data = response.data.data;
          suratData.value = {
            id: data.id,
            nomor: data.nomor || "",
            prodi_id: data.prodi_id || "",
            th_akademik_id: data.th_akademik_id
              ? Number(data.th_akademik_id)
              : null,
            nama: data.nama || "",
            tanggal_lahir: data.tanggal_lahir
              ? data.tanggal_lahir.slice(0, 10)
              : "",
            nim: data.nim || "",
            jurusan_prodi: data.jurusan_prodi || "",
            semester: data.semester || "",
            tahun_akademik: data.tahun_akademik || "",
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
        errors.value = {};
        loading.value = true;
        const response = await apiPut(
          `/surat-keterangan-transfer/${form.id}`,
          form
        );
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/skt" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda");
          } else {
            toast.error("Surat gagal diupdate", {
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
      errors,
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
    :errors="errors"
  />
</template>
