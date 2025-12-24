<script lang="ts">
import { defineComponent } from "vue";
import { onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import SuratComponent from "../../../components/surat_keterangan_aktif_mahasiswa/index.vue";
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
      nim: "",
      nik: "",
      tempat_lahir: "",
      tanggal_lahir: "",
      prodi_mhs: "",
      semester: "",
      tahun_akademik: "",
      nama_ortu: "",
      nik_ortu: "",
      nip_ortu: "",
      alamat_ortu: "",
      hp_ortu: "",
      tanggal: "",
    });

    async function getSurat() {
      try {
        loading.value = true;
        const id = route.params.id;
        const response = await apiGet(`/skam/${id}`);
        if (response.data.status) {
          const data = response.data.data;

          suratData.value = {
            id: data.id,
            prodi_id: data.prodi_id || 0,
            nama_mhs: data.nama_lengkap || "",
            nim: data.nim || "",
            nik: data.nik || "",
            tempat_lahir: data.tempat_lahir || "",
            tanggal_lahir: data.tanggal_lahir || "",
            prodi_mhs: data.prodi_mhs || "",
            semester: data.semester || "",
            tahun_akademik: data.tahun_akademik || "",
            nama_ortu: data.nama_ortu || "",
            nik_ortu: data.nik_ortu || "",
            nip_ortu: data.nip_ortu || "",
            alamat_ortu: data.alamat_ortu || "",
            hp_ortu: data.hp_ortu || "",
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
        const response = await apiPut(`/skam/${form.id}`, form);
        if (response.success == true) {
          toast.success("Surat berhasil diupdate", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          router.push({ path: "/skam" });
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
