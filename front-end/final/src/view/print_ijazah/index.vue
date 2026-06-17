<script lang="ts">
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiGet } from "../../services/api/request";
import SimpleCardComponent from "../../shared/components/@spk/simple-card.vue";
import router from "../../router";
import "vue3-toastify/dist/index.css";

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
      { text: "Nama Batch", value: "nama_batch", sortable: true },
      { text: "Jumlah Mahasiswa", value: "mahasiswa_count", sortable: false },
      { text: "Tanggal Import", value: "tanggal_import", sortable: true },
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
        const response = await apiGet("/batch", {
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

    function print(params: any) {
      router.push(`/print-ijazah/preview/${params.id}`);
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

    return {
      headers,
      items,
      getData,
      print,
      total,
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
      <h1 class="page-title fw-semibold fs-18 mb-0">Print Ijazah</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Aplikasi</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Print Ijazah</li>
          </ol>
        </nav>
      </div>
    </div>

    <SimpleCardComponent>
      <div class="row mb-3">
        <div class="col-md-3 ms-auto">
          <div class="input-group">
            <input
              type="text"
              class="form-control form-control-sm"
              v-model="searchValue"
              placeholder="Cari batch..."
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
          <template v-if="column === 'tanggal_import'">
            {{ item.tanggal_import ? new Date(item.tanggal_import).toLocaleDateString('id-ID') : '-' }}
          </template>

          <template v-else-if="column === 'action'">
            <div class="btn-list">
              <button
                class="btn btn-sm btn-icon btn-success-light btn-wave"
                title="Print Ijazah"
                @click="print(item)"
              >
                <i class="ri-printer-line"></i> Print
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
  width: auto;
  height: 2rem;
  padding: 0 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
</style>
