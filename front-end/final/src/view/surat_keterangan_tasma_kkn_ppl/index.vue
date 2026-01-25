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
import { apiPdf } from "@/services/api/request";

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
      { text: "No", value: "no", sortable: false },
      { text: "NIM", value: "nim", sortable: true },
      { text: "Nama Lengkap", value: "nama_lengkap", sortable: true },
      { text: "Prodi", value: "prodi_mhs", sortable: true },
      { text: "Kelas Pondok", value: "kelas_pondok", sortable: true },
      { text: "Ketua", value: "ketua", sortable: true },
      { text: "URL", value: "drive_link", sortable: false },
      { text: "Action", value: "action", sortable: false },
    ];

    const serverOptions = ref<ServerOptions>({
      page: 1,
      rowsPerPage: 10,
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
        const response = await apiGet("/sktkp", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
          prodi_id: prodiFilter.value,
        });

        const rows = Array.isArray(response.data.data.data)
          ? response.data.data.data
          : [];

        const startNo =
          (serverOptions.value.page - 1) * serverOptions.value.rowsPerPage;
        items.value = rows.map((row: any, idx: number) => ({
          ...row,
          no: startNo + idx + 1,
        }));

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
      router.push(`/sktkp/edit/${params.id}`);
    }

    async function remove(params: any) {
      if (!confirm("Apakah anda yakin ingin menghapus data ini?")) return;
      try {
        const response = await apiDelete("/sktkp/" + params.id);
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
          `/sktkp/download-pdf/${params.id}`,
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
      return router.push("/sktkp/add");
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
  <div class="container-fluid">
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Surat Keterangan TASMA</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Kegiatan</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">SKTKP</li>
          </ol>
        </nav>
      </div>
    </div>

    <SimpleCardComponent>
      <template #showheader>
        <div class="row g-2 align-items-center w-100 m-0">
          <div class="col-12 col-md-auto">
            <button
              class="btn btn-primary btn-wave shadow-sm w-100"
              @click="goAdd"
            >
              <i class="ri-add-line align-middle me-1"></i> Tambah Data
            </button>
          </div>
          <div class="col-12 col-md-auto ms-auto">
            <select
              class="form-select form-select-sm"
              style="min-width: 200px"
              v-model="prodiFilter"
            >
              <option value="">Semua Prodi Unit</option>
              <option v-for="item in listProdi" :key="item.id" :value="item.id">
                {{ item.nama }}
              </option>
            </select>
          </div>
        </div>
      </template>

      <div class="row mb-3">
        <div class="col-md-3 ms-auto">
          <div class="input-group">
            <input
              type="text"
              class="form-control form-control-sm"
              v-model="searchValue"
              placeholder="Cari data..."
            />
            <button class="btn btn-primary btn-sm" type="button">
              <i class="ri-search-line"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <EasyDataTable
          class="table text-nowrap"
          :headers="headers"
          :items="items"
          border-cell
          v-model:server-options="serverOptions"
          :loading="loading"
          :server-items-length="total"
          :rows-items="[10, 25, 50, 100]"
          buttons-pagination
        >
          <template #loading>
            <div class="text-center">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </template>

          <template #item-drive_link="item">
            <template v-if="item.drive_link">
              <a
                :href="item.drive_link"
                target="_blank"
                class="btn btn-sm btn-success-light"
              >
                <i class="ri-link"></i> Lihat
              </a>
            </template>
            <template v-else>
              <span class="badge bg-warning-transparent"
                >proses upload google drive</span
              >
            </template>
          </template>

          <template #item-action="item">
            <div class="btn-list">
              <button
                class="btn btn-sm btn-icon btn-info-light btn-wave"
                title="Download PDF"
                @click="download(item)"
              >
                <i class="ri-download-2-line"></i>
              </button>
              <button
                class="btn btn-sm btn-icon btn-primary-light btn-wave"
                title="Edit"
                @click="edit(item)"
              >
                <i class="ri-edit-line"></i>
              </button>
              <button
                class="btn btn-sm btn-icon btn-danger-light btn-wave"
                title="Hapus"
                @click="remove(item)"
              >
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </template>
        </EasyDataTable>
      </div>
    </SimpleCardComponent>
  </div>
</template>

<style scoped>
.btn-icon {
  width: 2rem;
  height: 2rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
