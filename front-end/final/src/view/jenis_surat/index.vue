<script lang="ts">
import Swal from "sweetalert2";
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiDelete, apiGet } from "../../services/api/request";
import SimpleCardComponent from "../../shared/components/@spk/simple-card.vue";
import Pageheader from "../../shared/components/pageheader/pageheader.vue";
import router from "../../router";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default defineComponent({
  components: { Pageheader, SimpleCardComponent },
  setup() {
    const items = ref([]);
    const searchValue = ref("");
    const total = ref(0);
    const headers = [
      { text: "No", value: "no", sortable: false },
      { text: "Nama", value: "nama", sortable: true },
      { text: "Alias", value: "alias", sortable: true },
      { text: "Format Surat", value: "format_surat", sortable: true },
      { text: "Action", value: "action", sortable: false },
    ];
    const serverOptions = ref<ServerOptions>({
      page: 1,
      rowsPerPage: 10,
      sortBy: "id",
      sortType: "desc",
    });
    const loading = ref(false);

    async function getData() {
      try {
        loading.value = true;
        const response = await apiGet("/jenis-surat", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
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
      } finally {
        loading.value = false;
      }
    }

    function edit(params: any) {
      router.push(`/jenis-surat/edit/${params.id}`);
    }

    async function remove(params: any) {
      
      Swal.fire({
        title: "Apakah anda yakin?",
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Ya, hapus!",
        cancelButtonText: "Batal",
      }).then(async (result) => {
        if (result.isConfirmed) {
      try {
        const response = await apiDelete("/jenis-surat/" + params.id);
        if (response.success && response.data?.status !== false) {
          toast.success("Jenis surat berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("Jenis surat gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat menghapus jenis surat", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
        }
      });
    }

    watch([searchValue], () => {
      getData();
    });
    watch([serverOptions], () => {
      getData();
    });
    onMounted(() => {
      getData();
    });

    function goAdd() {
      return router.push("/jenis-surat/add");
    }

    return {
      headers,
      items,
      getData,
      edit,
      remove,
      total,
      goAdd,
      serverOptions,
      searchValue,
      loading,
    };
  },
});
</script>

<template>
  <div class="container-fluid">
    <!-- Page Header with Breadcrumb -->
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Jenis Surat</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Master</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              Jenis Surat
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <SimpleCardComponent>
      <template #showheader>
        <button class="btn btn-primary btn-wave shadow-sm" @click="goAdd">
          <i class="ri-add-line align-middle me-1"></i> Tambah Data
        </button>
      </template>
      <!-- Search -->
      <div class="row mb-3">
        <div class="col-md-3 ms-auto">
          <div class="input-group">
            <input
              type="text"
              class="form-control form-control-sm"
              v-model="searchValue"
              placeholder="Cari jenis surat..."
            />
            <button class="btn btn-primary btn-sm" type="button">
              <i class="ri-search-line"></i>
            </button>
          </div>
        </div>
      </div>
      <!-- Data Table -->
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
          <div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </template>
        <template #item-action="item">
          <button
            class="btn btn-sm btn-warning me-1"
            @click="edit(item)"
          >
            <i class="ri-pencil-line"></i>
          </button>
          <button
            class="btn btn-sm btn-danger"
            @click="remove(item)"
          >
            <i class="ri-delete-bin-line"></i>
          </button>
        </template>
      </EasyDataTable>
    </SimpleCardComponent>
  </div>
</template>
