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
  // data() {
  //   return {
  //     datatoPass: {
  //       title: "Tables",
  //       currentpage: "Data tables",
  //       activepage: "Data Tables",
  //     },
  //     searchValue: "",
  //     searchValue1: "",
  //     searchValue2: "",
  //     searchValue3: "",
  //   };
  // },
  components: {
    Pageheader,
    SimpleCardComponent,
  },
  setup() {
    const items = ref([]);
    const searchValue1 = ref("");
    const searchValue2 = ref("");
    const searchValue3 = ref("");
    const total = ref(0);
    const headers = [
      { text: "Name", value: "name", sortable: true },
      { text: "Email", value: "email", sortable: true },
      { text: "Level", value: "level", sortable: true },
      { text: "No Hp", value: "phone", sortable: true },
      { text: "action", value: "action", sortable: false },
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
        // //page=${ServerOptions.page}&limit=${ServerOptions.rowsPerPage}&sortBy=${ServerOptions.sortBy}&sortType=${ServerOptions.sortType}
        // const url =`/data-users?page=${serverOptions.value.page}&limit=${serverOptions.value.rowsPerPage}&sortBy=${serverOptions.value.sortBy}&sortType=${serverOptions.value.sortType}&search=${searchValue3.value}`
        const response = await apiGet("/data-users", {
          page: serverOptions.value.page,
          limit: serverOptions.value.rowsPerPage,
          sortBy: serverOptions.value.sortBy,
          sortType: serverOptions.value.sortType,
          search: searchValue3.value,
        });
        console.log(response.data.data);

        // items.value = response.data.data;
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
      router.push(`/users/edit/${params.id}`);
    }
    async function remove(params: any) {
      try {
        const response = await apiDelete("/data-users/" + params.id);
        if (response.data.status || response.success) {
          toast.success("User berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("User gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        console.log(error);
        toast.error("Terjadi kesalahan saat menghapus user", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
      }
    }

    watch([searchValue3], () => {
      getData();
    });

    watch([serverOptions], () => {
      getData();
    });

    onMounted(() => {
      getData();
    });

    function goAdd() {
      console.log("masuk kok");

      return router.push("/users/add");
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
      searchValue1,
      searchValue2,
      searchValue3,
      loading,
    };
  },
});
</script>
<!-- <style>
.easy-data-table {
  background-color: transparent !important;
  border: none !important;
  box-shadow: none !important;
}

.easy-data-table__header,
.easy-data-table__footer {
  background-color: transparent !important;
  border: none !important;
}
</style> -->

<template>
  <h3>Users</h3>
  <SimpleCardComponent>
    <template #showheader>
      <button class="btn btn-primary btn-sm" @click="goAdd">Tambah Data</button>
    </template>
    <label class="mb-3">
      <input
        type="text"
        class="form-control form-control-sm"
        v-model="searchValue3"
        placeholder="Search value"
      />
    </label>
    <EasyDataTable
      class="table text-nowrap"
      :search-value="searchValue3"
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
      <!-- <template #item-action="{ item }">
        <button @click="edit(item)">Edit</button>
      </template> -->
      <template #item="{ item, column }">
        <!-- kolom Action -->
        <template v-if="column === 'action'">
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

      <!-- <template #expand="item">
      <div style="padding: 15px">Additional Details of {{ item.name }}</div>
    </template> -->
    </EasyDataTable>
  </SimpleCardComponent>
</template>
