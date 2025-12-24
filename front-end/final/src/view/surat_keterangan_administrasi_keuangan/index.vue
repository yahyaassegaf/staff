<script lang="ts">
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiDelete, apiGet } from "@/services/api/request";
import SimpleCardComponent from "@/shared/components/@spk/simple-card.vue";
import Pageheader from "@/shared/components/pageheader/pageheader.vue";
import router from "@/router";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { apiPdf } from "../../services/api/request";

export default defineComponent({
  components: {
    Pageheader,
    SimpleCardComponent,
  },
  setup() {
    const items = ref([]);
    const searchValue = ref("");
    const total = ref(0);
    const headers = [
      { text: "NIM", value: "nim", sortable: true },
      { text: "Nama Lengkap", value: "nama_lengkap", sortable: true },
      { text: "Nama Prodi", value: "nama_prodi", sortable: true },
      { text: "Kelas Pondok", value: "kelas_pondok", sortable: true },
      { text: "Action", value: "action", sortable: false },
    ];

    const serverOptions = ref<ServerOptions>({
      page: 1,
      rowsPerPage: 5,
      sortBy: "id",
      sortType: "desc",
    });

    const loading = ref(false);

    const listProdi = ref<any[]>([]);
    const prodiFilter = ref("");

    async function getProdi() {
      try {
        const response = await apiGet(`/get-prodi`);
        if (response.success) {
          const data = response.data?.data;
          listProdi.value = Array.isArray(data) ? data : [data];
        }
      } catch (error) {
        console.log(error);
      }
    }

    async function getData() {
      try {
        loading.value = true;
        const response = await apiGet("/skak", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
          prodi_id: prodiFilter.value,
        });

        const respData = response.data; // Adjusted based on controller response structure (usually wrapped)
        // Controller returns: ['status'=>true, 'data'=> paginate object, 'message'=>...]

        items.value = Array.isArray(respData.data.data)
          ? respData.data.data
          : [];

        total.value = respData.data.total;
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }

    function openFileExport(blob: any) {
      const url = URL.createObjectURL(blob);
      window.open(url, "_blank", "noopener");
      setTimeout(() => URL.revokeObjectURL(url), 10_000);
    }

    function edit(params: any) {
      router.push(`/skak/edit/${params.id}`);
    }

    async function remove(params: any) {
      if (!confirm("Are you sure you want to delete this item?")) return;
      try {
        const response = await apiDelete("/skak/" + params.id);
        if (response.data.status || response.success) {
          toast.success("Data berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("Data gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat menghapus data", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
    }

    watch([searchValue, prodiFilter], () => {
      getData();
    });

    async function download(params: any) {
      try {
        const res = await apiPdf(
          `/skak/download-pdf/${params.id}`,
          {},
          { responseType: "blob" }
        );

        console.log(res.data);
        openFileExport(res.data);
      } catch (error) {
        console.log(error);
        toast.error("Gagal mengunduh file PDF", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
    }

    watch([serverOptions], () => {
      getData();
    });

    onMounted(() => {
      getProdi();
      getData();
    });

    function goAdd() {
      return router.push("/skak/add");
    }

    return {
      headers,
      items,
      getData,
      edit,
      remove,
      download,
      total,
      goAdd,
      serverOptions,
      searchValue,
      loading,
      listProdi,
      prodiFilter,
    };
  },
});
</script>

<template>
  <h3>Surat Keterangan Administrasi Keuangan</h3>
  <SimpleCardComponent>
    <template #showheader>
      <div class="d-flex justify-content-between align-items-center w-100">
        <button class="btn btn-primary btn-sm" @click="goAdd">
          Tambah Data
        </button>
        <select
          class="form-select form-select-sm"
          style="width: 200px"
          v-model="prodiFilter"
        >
          <option value="">Semua Prodi</option>
          <option v-for="prodi in listProdi" :key="prodi.id" :value="prodi.id">
            {{ prodi.nama }}
          </option>
        </select>
      </div>
    </template>
    <div class="row mb-3">
      <div class="col-md-3 ms-auto">
        <input
          type="text"
          class="form-control form-control-sm"
          v-model="searchValue"
          placeholder="Cari..."
        />
      </div>
    </div>
    <EasyDataTable
      class="table text-nowrap"
      :search-value="searchValue"
      :headers="headers"
      :items="items"
      border-cell
      v-model:server-options="serverOptions"
      :loading="loading"
      :server-items-length="total"
      :rowsItems="[5, 10, 25, 50, 100]"
    >
      <template #loading>
        <div class="text-center">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </template>
      <template #item="{ item, column }">
        <!-- kolom Action -->
        <template v-if="column === 'action'">
          <button class="btn btn-sm btn-primary" @click="edit(item)">
            Edit
          </button>
          <button class="btn btn-sm btn-danger ms-1" @click="remove(item)">
            Delete
          </button>
          <button class="btn btn-sm btn-info ms-1" @click="download(item)">
            PDF
          </button>
        </template>

        <!-- kolom lain -->
        <template v-else>
          {{ item[column] }}
        </template>
      </template>
    </EasyDataTable>
  </SimpleCardComponent>
</template>
