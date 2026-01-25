<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import FormComponent from "../../../components/surat_pernyataan_verifikasi_nilai/index.vue";
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
      tanda_tangan_id: null as null | number,
      nama_penandatangan: "",
      niy: "",
      jabatan: "",
      nama_mahasiswa: "",
      nim: "",
      prodi: "",
      fakultas: "",
      tanggal: "",
      prodi_id: "",
      jenis_kelamin: "L",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/spvn/${id}`);
        if (response.data.status) {
          const data = response.data.data;
          suratData.value = {
            id: data.id,
            nomor: data.nomor || "",
            tanda_tangan_id: data.tanda_tangan_id
              ? Number(data.tanda_tangan_id)
              : null,
            nama_penandatangan: data.nama_penandatangan || "",
            niy: data.niy || "",
            jabatan: data.jabatan || "",
            nama_mahasiswa: data.nama_mahasiswa || "",
            nim: data.nim || "",
            prodi: data.prodi_mhs || "",
            fakultas: data.fakultas || "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
            prodi_id: data.prodi_id || "",
            jenis_kelamin: data.jenis_kelamin || "L",
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
        const response = await apiPut(`/spvn/${form.id}`, form);
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/spvn" });
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
      errors,
    };
  },
});
</script>

<template>
  <FormComponent
    @submit="submit"
    :modelValue="suratData"
    :isEdit="true"
    :errors="errors"
  />
</template>
