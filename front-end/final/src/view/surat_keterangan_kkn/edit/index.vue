<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import SuratComponent from "../../../components/surat_keterangan_kkn/index.vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiGet, apiPut } from "../../../services/api/request";
import router from "../../../router";

export default defineComponent({
  components: {
    SuratComponent,
  },
  setup() {
    const route = useRoute();
    const loading = ref(false);
    const errors = ref<any>({});
    const suratData = ref({
      id: "",
      prodi_id: 0,
      ketua: "",
      nama_mhs: "",
      tempat_lahir: "",
      tanggal_lahir: "",
      nim: "",
      jenis_kelamin: "",
      prodi_mhs: "",
      alamat_rumah: "",
      kelas_pondok: "",
      tanggal: "",
      tanda_tangan_id: null as null | number,
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/skk/${id}`);
        if (response.data.status) {
          const data = response.data.data;

          suratData.value = {
            id: data.id,
            prodi_id: data.prodi_id || 0,
            ketua: data.ketua || "",
            nama_mhs: data.nama_lengkap || "",
            tempat_lahir: data.tempat_lahir || "",
            tanggal_lahir: data.tanggal_lahir
              ? data.tanggal_lahir.slice(0, 10)
              : "",
            nim: data.nim || "",
            jenis_kelamin: data.jenis_kelamin || null,
            prodi_mhs: data.prodi_mhs || "",
            alamat_rumah: data.alamat_rumah || "",
            kelas_pondok: data.kelas_pondok || "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
            tanda_tangan_id: data.tanda_tangan_id
              ? Number(data.tanda_tangan_id)
              : null,
          };
        }
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }

    async function submit(form: any) {
      try {
        errors.value = {};
        loading.value = true;
        const response = await apiPut(`/skk/${form.id}`, form);
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
            isLoading: false,
          });
          router.push({ path: "/skk" });
        } else {
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            toast.error("Validasi gagal, mohon periksa kembali inputan Anda", {
              isLoading: false,
            });
          } else {
            toast.error("Surat gagal diupdate", {
              theme: "auto",
              icon: true,
              hideProgressBar: true,
              autoClose: true,
              position: "top-right",
              isLoading: false,
            });
          }
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
          isLoading: false,
        });
      } finally {
        loading.value = false;
      }
    }

    onMounted(() => {
      getSurat();
    });

    return {
      getSurat,
      suratData,
      submit,
      loading,
      errors,
    };
  },
});
</script>
<template>
  <SuratComponent
    @submit="submit"
    :modelValue="suratData"
    :isEdit="true"
    :errors="errors"
  />
</template>
