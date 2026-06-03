<script lang="ts">
import { defineComponent, onMounted, ref } from "vue";
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
    const errors = ref<any>({});
    const suratData = ref({
      id: "",
      no_surat: "",
      prodi_id: 0,
      th_akademik_id: null as null | number,
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
      tanda_tangan_id: null as null | number,
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
            no_surat: data.no_surat || data.nomor_surat || data.nomor || "",
            tanda_tangan_id: data.tanda_tangan_id ? Number(data.tanda_tangan_id) : null,
            prodi_id: data.prodi_id || 0,
            th_akademik_id: data.th_akademik_id
              ? Number(data.th_akademik_id)
              : null,
            nama_mhs: data.nama_lengkap || "",
            nim: data.nim || "",
            nik: data.nik || "",
            tempat_lahir: data.tempat_lahir || "",
            tanggal_lahir: data.tanggal_lahir
              ? data.tanggal_lahir.slice(0, 10)
              : "",
            prodi_mhs: data.prodi_mhs || "",
            semester: data.semester || "",
            tahun_akademik: data.tahun_akademik || "",
            nama_ortu: data.nama_ortu || "",
            nik_ortu: data.nik_ortu || "",
            nip_ortu: data.nip_ortu || "",
            alamat_ortu: data.alamat_ortu || "",
            hp_ortu: data.hp_ortu || "",
            tanggal: data.tanggal ? data.tanggal.slice(0, 10) : "",
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
          if ((response.error as any)?.response?.status === 422) {
            errors.value = (response.error as any).response.data.errors;
            
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
    :btnLoading="loading"
  />
</template>
