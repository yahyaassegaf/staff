<script lang="ts">
import Swal from "sweetalert2";
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiDelete, apiGet } from "../../services/api/request";
import SimpleCardComponent from "../../shared/components/@spk/simple-card.vue";
import router from "../../router";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { BASE_URL } from "../../services/api/http";

export default defineComponent({
  components: {
    SimpleCardComponent,
  },
  setup() {
    const items = ref([]);
    const searchValue = ref("");
    const total = ref(0);
    const headers = [
      { text: "No", value: "no", sortable: false },
      { text: "Kunci Jabatan", value: "kunci_jabatan", sortable: true },
      { text: "Nama Jabatan", value: "nama_jabatan", sortable: true },
      { text: "NIDN", value: "nidn", sortable: true },
      { text: "Nama Tanda Tangan", value: "tanda_tangan.nama", sortable: false },
      { text: "Canvas TDD", value: "tdd", sortable: false },
      { text: "Gambar Upload", value: "gambar", sortable: false },
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
        const response = await apiGet("/setting-jabatan", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
        });

        if (response.success) {
          const rows = response.data.data.data || [];
          const startNo =
            (serverOptions.value.page - 1) * serverOptions.value.rowsPerPage;
          items.value = rows.map((row: any, idx: number) => ({
            ...row,
            no: startNo + idx + 1,
          }));
          total.value = response.data.data.total || 0;
        }
      } catch (error) {
      } finally {
        loading.value = false;
      }
    }

    function edit(params: any) {
      router.push(`/setting-jabatan/edit/${params.id}`);
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
        const response = await apiDelete("/setting-jabatan/" + params.id);
        if (response.success || response.data.status) {
          toast.success("Data berhasil dihapus");
          getData();
        } else {
          toast.error("Data gagal dihapus");
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat menghapus data");
      }
        }
      });
    }

    watch([searchValue], () => {
      serverOptions.value.page = 1;
      getData();
    });

    watch(
      serverOptions,
      () => {
        getData();
      },
      { deep: true }
    );

    onMounted(() => {
      getData();
    });

    function goAdd() {
      router.push("/setting-jabatan/add");
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
      BASE_URL,
    };
  },
});
</script>

<template>
  <div class="container-fluid">
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Setting Jabatan</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Master</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              Setting Jabatan
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

      <div class="row mb-3">
        <div class="col-md-3 ms-auto">
          <div class="input-group">
            <input
              type="text"
              class="form-control form-control-sm"
              v-model="searchValue"
              placeholder="Cari Kunci atau Nama..."
            />
            <button class="btn btn-primary btn-sm" type="button">
              <i class="ri-search-line"></i>
            </button>
          </div>
        </div>
      </div>

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

        <template #item-tdd="item">
          <img
            v-if="item.tanda_tangan && item.tanda_tangan.tdd"
            :src="item.tanda_tangan.tdd"
            class="img-thumbnail"
            style="height: 50px; background: white"
          />
          <span v-else class="text-muted small">Tidak ada</span>
        </template>

        <template #item-gambar="item">
          <img
            v-if="item.tanda_tangan && item.tanda_tangan.gambar"
            :src="BASE_URL.replace('/api', '') + '/' + item.tanda_tangan.gambar"
            class="img-thumbnail"
            style="height: 50px; background: white"
          />
          <span v-else class="text-muted small">Tidak ada</span>
        </template>

        <template #item-action="item">
          <div class="btn-list">
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
    </SimpleCardComponent>
  </div>
</template>

<style scoped>
.btn-icon {
  width: 2rem;
  height: 2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
