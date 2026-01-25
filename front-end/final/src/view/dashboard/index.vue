<script lang="ts">
import { onMounted, ref, watch } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiGet } from "../../services/api/request";
import SimpleCardComponent from "../../shared/components/@spk/simple-card.vue";
import CoursesDashboard from "../../components/dashboards/courses.vue";

export default defineComponent({
  components: {
    SimpleCardComponent,
    CoursesDashboard,
  },
  setup() {
    const items = ref<any[]>([]);
    const searchValue = ref("");
    const total = ref(0);

    const cards = ref<any[]>([]);

    const headers = [
      { text: "No", value: "no", sortable: false },
      { text: "Nomor", value: "nomor", sortable: true },
      { text: "Nomor Surat", value: "nomor_surat", sortable: true },
      { text: "Nama Surat", value: "nama_surat", sortable: true },
      { text: "Tanggal", value: "created_at", sortable: true },
    ];

    const serverOptions = ref<ServerOptions>({
      page: 1,
      rowsPerPage: 10,
      sortBy: "id",
      sortType: "desc",
    });

    const loadingTable = ref(false);

    function buildCards(payload: any) {
      const totalSurat = payload?.cards?.total ?? 0;
      const pending = payload?.cards?.pending ?? 0;
      const uploaded = payload?.cards?.uploaded ?? 0;

      cards.value = [
        {
          id: 1,
          title: "Total Surat",
          value: totalSurat,
          percent: "",
          iconClass: "up",
          svgColor: "primary",
          svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368">
                <path d="M0 0h24v24H0z" fill="none" />
                <path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z" />
            </svg>`,
        },
        {
          id: 2,
          title: "Pending",
          value: pending,
          percent: "",
          iconClass: "up",
          svgColor: "warning",
          svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368">
                <path d="M0 0h24v24H0z" fill="none" />
                <path d="M12 8v5l4.3 2.6-.8 1.3L11 14V8h1zm0-6C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8z" />
            </svg>`,
        },
        {
          id: 3,
          title: "Uploaded",
          value: uploaded,
          percent: "",
          iconClass: "up",
          svgColor: "success",
          svgIcon: `<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#5f6368">
                <path d="M0 0h24v24H0z" fill="none" />
                <path d="M9 16.2l-3.5-3.5L4 14.2 9 19l12-12-1.4-1.4z" />
            </svg>`,
        },
      ];
    }

    buildCards({ cards: { total: 0, pending: 0, uploaded: 0 } });

    async function getCards() {
      try {
        const response = await apiGet("/dashboard/cards");
        if (response.success && response.data?.status) {
          buildCards({ cards: response.data.data });
        }
      } catch (error) {
        console.log(error);
      }
    }

    async function getLogs() {
      try {
        loadingTable.value = true;
        const response = await apiGet("/dashboard", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
        });

        if (response.success && response.data?.status) {
          const payload = response.data.data;
          const startNo =
            (serverOptions.value.page - 1) * serverOptions.value.rowsPerPage;
          items.value = (payload.data || []).map((row: any, idx: number) => ({
            ...row,
            no: startNo + idx + 1,
          }));
          total.value = payload.total || 0;
        }
      } catch (error) {
        console.log(error);
      } finally {
        loadingTable.value = false;
      }
    }

    watch([searchValue], () => {
      serverOptions.value.page = 1;
      getLogs();
    });

    watch(
      serverOptions,
      () => {
        getLogs();
      },
      { deep: true }
    );

    onMounted(() => {
      getCards();
      getLogs();
    });

    return {
      headers,
      items,
      total,
      serverOptions,
      searchValue,
      loadingTable,
      cards,
    };
  },
});
</script>

<template>
  <div class="container-fluid">
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Dashboard</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Home</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
          </ol>
        </nav>
      </div>
    </div>

    <CoursesDashboard :cards="cards" />

    <div class="mt-4">
      <SimpleCardComponent>
        <div class="row mb-3">
          <div class="col-md-3 ms-auto">
            <div class="input-group">
              <input
                type="text"
                class="form-control form-control-sm"
                v-model="searchValue"
                placeholder="Cari Log Surat..."
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
          :loading="loadingTable"
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
        </EasyDataTable>
      </SimpleCardComponent>
    </div>
  </div>
</template>

<style scoped></style>
