<script lang="ts">
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
      { text: "Nama Kepala", value: "nama_kepala", sortable: true },
      { text: "Kode", value: "kode", sortable: true },
      { text: "Nama", value: "nama", sortable: true },
      { text: "Jenjang", value: "jenjang", sortable: true },
      { text: "Action", value: "action", sortable: false },
    ];

    const serverOptions = ref<ServerOptions>({
      page: 1,
      rowsPerPage: 5,
      sortBy: "id",
      sortType: "desc",
    });

    const loading = ref(false);

    async function getData() {
      try {
        loading.value = true;
        const response = await apiGet("/prodi", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue.value,
        });
        console.log(response.data.data);

        items.value = Array.isArray(response.data.data.data)
          ? response.data.data.data
          : [];

        total.value = response.data.data.total;
        console.log("total data ", total.value);

        console.log("ITEMS:", items.value);
      } catch (error) {
        console.log(error);
      } finally {
        loading.value = false;
      }
    }

    function edit(params: any) {
      console.log(params);
      router.push(`/prodi/edit/${params.id}`);
    }

    async function remove(params: any) {
      try {
        const response = await apiDelete("/prodi/" + params.id);
        if (response.data.status || response.success) {
          toast.success("Prodi berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("Prodi gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat menghapus prodi", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
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
      return router.push("/prodi/add");
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
  <h3>Prodi</h3>
  <SimpleCardComponent>
    <template #showheader>
      <button class="btn btn-primary btn-sm" @click="goAdd">Tambah Data</button>
    </template>
    <label class="mb-3">
      <input
        type="text"
        class="form-control form-control-sm"
        v-model="searchValue"
        placeholder="Cari prodi..."
      />
    </label>
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
        <!-- kolom Status -->
        <template v-if="column === 'aktif'">
          <span
            :class="item.aktif === 'Y' ? 'badge bg-success' : 'badge bg-danger'"
          >
            {{ item.aktif === "Y" ? "Aktif" : "Tidak Aktif" }}
          </span>
        </template>

        <!-- kolom Action -->
        <template v-else-if="column === 'action'">
          <button class="btn btn-sm btn-primary" @click="edit(item)">
            Edit
          </button>
          <button class="btn btn-sm btn-danger ms-1" @click="remove(item)">
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
