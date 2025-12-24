<script lang="ts">
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiDelete, apiGet } from "@/services/api/request";
import SimpleCardComponent from "@/shared/components/@spk/simple-card.vue";
import Pageheader from "@/shared/components/pageheader/pageheader.vue";
import router from "@/router";
import { BASE_URL } from "@/services/api/http";
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
      { text: "NIm", value: "nim", sortable: true },
      { text: "Nama Lengkap", value: "nama_lengkap", sortable: true },
      { text: "Nama Prodi", value: "nama_prodi", sortable: true },
      { text: "Prodi", value: "alias_prodi", sortable: true },
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
        const response = await apiGet("/sklmk", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
          prodi_id: prodiFilter.value,
        });

        items.value = Array.isArray(response.data.data.data)
          ? response.data.data.data
          : [];

        total.value = response.data.data.total;
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
      console.log(params.id);
      router.push(`/sklmk/edit/${params.id}`);
    }

    async function remove(params: any) {
      try {
        const response = await apiDelete("/sklmk/" + params.id);
        if (response.data.status || response.success) {
          toast.success("Fakultas berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("Fakultas gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat menghapus fakultas", {
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
          `/download-pdf/${params.id}`,
          {},
          { responseType: "blob" }
        );

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
      return router.push("/sklmk/add");
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
  <h3>Daftar Fakultas</h3>
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
          <option v-for="item in listProdi" :key="item.id" :value="item.id">
            {{ item.nama }}
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
          placeholder="Cari fakultas..."
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
            Delete
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
