<script lang="ts">
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiGet } from "../../services/api/request";
import SimpleCardComponent from "../../shared/components/@spk/simple-card.vue";
import Pageheader from "../../shared/components/pageheader/pageheader.vue";
import router from "../../router";
import { toast } from "vue3-toastify";

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
      { text: "Nama", value: "nama", sortable: true },
      { text: "NIM", value: "nim", sortable: true },
      { text: "NIK", value: "nik", sortable: true },
      { text: "Prodi", value: "nama_prodi", sortable: true },
      { text: "Status", value: "status", sortable: true },
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
        const response = await apiGet("/transkip", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
        });

        const rows = Array.isArray(response.data?.data?.data)
          ? response.data.data.data
          : [];

        const startNo =
          (serverOptions.value.page - 1) * serverOptions.value.rowsPerPage;
        items.value = rows.map((row: any, idx: number) => ({
          ...row,
          no: startNo + idx + 1,
        }));

        total.value = response.data?.data?.total || 0;
      } catch (error) {
        toast.error("Gagal mengambil data mahasiswa", {
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

    function detail(params: any) {
      router.push(`/transkip/detail/${params.id}`);
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
      detail,
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
      <h1 class="page-title fw-semibold fs-18 mb-0">Transkrip Nilai</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Akademik</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Transkrip Nilai</li>
          </ol>
        </nav>
      </div>
    </div>

    <SimpleCardComponent>
      <template #showheader>
        <div class="d-flex gap-2">
        </div>
      </template>

      <div class="row mb-3">
        <div class="col-md-3 ms-auto">
          <div class="input-group">
            <input
              type="text"
              class="form-control form-control-sm"
              v-model="searchValue"
              placeholder="Cari mahasiswa..."
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
          <template v-if="column === 'status'">
            <span
              :class="
                item.status === 'sudah' ? 'badge bg-success' : 'badge bg-warning'
              "
            >
              {{ item.status === 'sudah' ? 'Sudah' : 'Belum' }}
            </span>
          </template>

          <template v-else-if="column === 'nama_prodi'">
            {{ item.prodi?.nama || '-' }}
          </template>

          <template v-else-if="column === 'action'">
            <div class="btn-list">
              <button
                class="btn btn-sm btn-icon btn-info-light btn-wave"
                title="Lihat / Cetak Transkrip"
                @click="detail(item)"
              >
                <i class="ri-printer-line"></i>
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
