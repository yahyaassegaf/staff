<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import SuratComponent from "../../../components/surat_keterangan_lulus_mata_kuliah/index.vue";
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
    const suratData = ref({
      id: "",
      prodi_id: 0,
      nama_mhs: "",
      tempat_lahir: "",
      tanggal_lahir: "",
      nim: "",
      prodi_mhs: "",
      alamat_rumah: "",
      kelas_pondok: "",
      tanggal: "",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/sklmk/${id}`);
        if (response.data.status) {
          const data = response.data.data;
          const clone = JSON.parse(JSON.stringify(data));

          console.log("isi data CLONE", clone);
          console.log("isi data prodi", clone.prodi_mahasiswa);

          suratData.value = {
            id: data.id,
            prodi_id: data.prodi_id || 0,
            nama_mhs: data.nama_lengkap || "",
            tempat_lahir: data.tempat_lahir || "",
            tanggal_lahir: data.tanggal_lahir || "",
            nim: data.nim || "",
            prodi_mhs: data.prodi_mahasiswa || "",
            alamat_rumah: data.alamat_rumah || "",
            kelas_pondok: data.kelas_pondok || "",
            tanggal: data.tanggal || "",
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
        loading.value = true;
        const response = await apiPut(`/sklmk/${form.id}`, form);
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/sklmk" });
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
        toast.error("Terjadi kesalahan", {
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
      getSurat,
      suratData,
      submit,
      loading,
    };
  },
});
</script>
<template>
  <SuratComponent @submit="submit" :modelValue="suratData" :isEdit="true" />
</template>
