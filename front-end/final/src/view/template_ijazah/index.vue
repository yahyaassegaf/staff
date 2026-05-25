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
      { text: "Nama Template", value: "nama_template", sortable: true },
      { text: "Prodi", value: "nama_prodi", sortable: true },
      { text: "Jenjang", value: "jenjang", sortable: true },
      { text: "Ukuran", value: "ukuran_kertas", sortable: true },
      { text: "Orientasi", value: "orientasi", sortable: true },
      { text: "Status", value: "is_active", sortable: true },
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
        const response = await apiGet("/template-ijazah", {
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
        toast.error("Gagal mengambil data template ijazah", {
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

    function edit(params: any) {
      router.push(`/template-ijazah/edit/${params.id}`);
    }

    function editor(params: any) {
      router.push(`/template-ijazah/editor/${params.id}`);
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
        const response = await apiDelete("/template-ijazah/" + params.id);
        if (response.success && response.data?.status !== false) {
          toast.success("Template ijazah berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("Template ijazah gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat menghapus template ijazah", {
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
      return router.push("/template-ijazah/add");
    }

    return {
      headers,
      items,
      getData,
      edit,
      editor,
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
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Template Ijazah</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Master</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Template Ijazah</li>
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
              placeholder="Cari template..."
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
        <template #item="{ item, column }">
          <template v-if="column === 'jenjang'">
            <span class="badge bg-info">
              {{ item.jenjang || 'Semua' }}
            </span>
          </template>

          <template v-else-if="column === 'orientasi'">
            <span :class="item.orientasi === 'portrait' ? 'badge bg-primary' : 'badge bg-secondary'">
              {{ item.orientasi === 'portrait' ? 'Portrait' : 'Landscape' }}
            </span>
          </template>

          <template v-else-if="column === 'is_active'">
            <span
              :class="
                item.is_active === 'aktif' ? 'badge bg-success' : 'badge bg-danger'
              "
            >
              {{ item.is_active === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
            </span>
          </template>

          <template v-else-if="column === 'nama_prodi'">
            {{ item.nama_prodi || 'Global (Semua Prodi)' }}
          </template>

          <template v-else-if="column === 'action'">
            <div class="btn-list">
              <button
                class="btn btn-sm btn-icon btn-success-light btn-wave"
                title="Editor Posisi"
                @click="editor(item)"
              >
                <i class="ri-layout-masonry-line"></i>
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

          <template v-else>
            {{ item[column] }}
          </template>
        </template>
      </EasyDataTable>
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
