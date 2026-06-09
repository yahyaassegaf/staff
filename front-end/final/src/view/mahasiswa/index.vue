<script lang="ts">
import Swal from "sweetalert2";
import { onMounted, ref, watch, nextTick, onBeforeUnmount } from "vue";
import { defineComponent } from "vue";
import type { ServerOptions } from "vue3-easy-data-table";
import { apiDelete, apiGet, apiPost } from "../../services/api/request";
import SimpleCardComponent from "../../shared/components/@spk/simple-card.vue";
import Pageheader from "../../shared/components/pageheader/pageheader.vue";
import router from "../../router";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { Modal } from "bootstrap";
import * as XLSX from "xlsx";

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
    const importing = ref(false);
    const fileInput = ref<HTMLInputElement | null>(null);
    const importModalRef = ref<HTMLElement | null>(null);
    const selectedFile = ref<File | null>(null);
    const isDragging = ref(false);
    let importModal: Modal | null = null;

    // Modal controls
    function openImportModal() {
      selectedFile.value = null;
      if (fileInput.value) fileInput.value.value = "";
      nextTick(() => {
        if (importModalRef.value) {
          importModal = new Modal(importModalRef.value);
          importModal.show();
        }
      });
    }

    function closeImportModal() {
      importModal?.hide();
    }

    // File handling
    function triggerFileSelect() {
      fileInput.value?.click();
    }

    function onFileSelected(event: Event) {
      const target = event.target as HTMLInputElement;
      const file = target.files?.[0];
      if (file) {
        if (validateFile(file)) {
          selectedFile.value = file;
        } else {
          target.value = "";
        }
      }
    }

    function onDragOver(event: DragEvent) {
      event.preventDefault();
      isDragging.value = true;
    }

    function onDragLeave() {
      isDragging.value = false;
    }

    function onDrop(event: DragEvent) {
      event.preventDefault();
      isDragging.value = false;
      const file = event.dataTransfer?.files?.[0];
      if (file) {
        if (validateFile(file)) {
          selectedFile.value = file;
        }
      }
    }

    function removeFile() {
      selectedFile.value = null;
      if (fileInput.value) fileInput.value.value = "";
    }

    function validateFile(file: File): boolean {
      const validExtensions = [".xlsx", ".xls", ".csv"];
      const extension = file.name
        .substring(file.name.lastIndexOf("."))
        .toLowerCase();
      if (!validExtensions.includes(extension)) {
        toast.error("Format file tidak valid. Gunakan .xlsx, .xls, atau .csv", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
        return false;
      }

      if (file.size > 10 * 1024 * 1024) {
        toast.error("Ukuran file maksimal 10MB", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
        return false;
      }
      return true;
    }

    function formatFileSize(bytes: number): string {
      if (bytes === 0) return "0 Bytes";
      const k = 1024;
      const sizes = ["Bytes", "KB", "MB", "GB"];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
    }

    // Download template
    function downloadTemplate() {
      // Data dummy menyesuaikan dengan gambar contoh
      const templateData = [
        {
          "Nomor SK BAN-PT": "No. 1691/SK/BAN-PT/Akred/S/VIII/2016",
          "Nilai Akreditasi": "Akreditasi B",
          "Nomor Ijazah Nasional": "862082021000012",
          "Nama": "DAVID RIZKI",
          "Tempat tanggal Lahir": "Indramayu, 14 Maret 1997",
          "NIM": "2014.85.01.1371",
          "NIK": "3212101403970007",
          "Tanggal SK Yudisium": "10 Januari 2021",
          "Tanggal Penerbitan": "10 Januari 2021",
        },
        {
          "Nomor SK BAN-PT": "No. 1691/SK/BAN-PT/Akred/S/VIII/2016",
          "Nilai Akreditasi": "Akreditasi B",
          "Nomor Ijazah Nasional": "862082021000029",
          "Nama": "ABDURRAHMAN ALI",
          "Tempat tanggal Lahir": "Jakarta, 02 November 1996",
          "NIM": "2014.85.01.1392",
          "NIK": "3175030211960006",
          "Tanggal SK Yudisium": "10 Januari 2021",
          "Tanggal Penerbitan": "10 Januari 2021",
        }
      ];

      const workbook = XLSX.utils.book_new();
      
      // Daftar sheet sesuai gambar
      const prodiSheets = ["PAI", "PBA", "MPI", "HKI", "ESY", "BKI", "KPI", "SPI"];
      
      prodiSheets.forEach(sheetName => {
        // Clone data agar tidak referensi silang jika dimodifikasi
        const sheetData = JSON.parse(JSON.stringify(templateData));
        const ws = XLSX.utils.json_to_sheet(sheetData);
        
        ws["!cols"] = [
          { wch: 35 }, // Nomor SK BAN-PT
          { wch: 20 }, // Nilai Akreditasi
          { wch: 25 }, // Nomor Ijazah Nasional
          { wch: 30 }, // Nama
          { wch: 35 }, // Tempat tanggal Lahir
          { wch: 20 }, // NIM
          { wch: 25 }, // NIK
          { wch: 25 }, // Tanggal SK Yudisium
          { wch: 25 }, // Tanggal Penerbitan
        ];
        
        XLSX.utils.book_append_sheet(workbook, ws, sheetName);
      });

      XLSX.writeFile(workbook, "template_import_mahasiswa.xlsx");

      toast.success("Template berhasil diunduh!", {
        theme: "auto",
        icon: true,
        hideProgressBar: true,
        autoClose: true,
        position: "top-right",
      });
    }

    // Import data
    async function importData() {
      if (!selectedFile.value) {
        toast.error("Silakan pilih file terlebih dahulu", {
          theme: "auto",
          icon: true,
          hideProgressBar: true,
          autoClose: true,
          position: "top-right",
        });
        return;
      }

      try {
        importing.value = true;
        const formData = new FormData();
        formData.append("file", selectedFile.value);

        const response = await apiPost("/mahasiswa/import", formData);

        if (response.success && response.data.status) {
          const { success, failed } = response.data.data;
          toast.success(`Import selesai. ${success} berhasil, ${failed} gagal`, {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          closeImportModal();
          getData();
        } else {
          toast.error(response.data.message || "Import gagal", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error: any) {
        toast.error(
          error.response?.data?.message || "Terjadi kesalahan saat import",
          {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          }
        );
      } finally {
        importing.value = false;
        selectedFile.value = null;
        if (fileInput.value) {
          fileInput.value.value = "";
        }
      }
    }

    async function getData() {
      try {
        loading.value = true;
        const response = await apiGet("/mahasiswa", {
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

    function edit(params: any) {
      router.push(`/mahasiswa/edit/${params.id}`);
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
        const response = await apiDelete("/mahasiswa/" + params.id);
        if (response.success && response.data?.status !== false) {
          toast.success("Mahasiswa berhasil dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
          getData();
        } else {
          toast.error("Mahasiswa gagal dihapus", {
            theme: "auto",
            icon: true,
            hideProgressBar: true,
            autoClose: true,
            position: "top-right",
          });
        }
      } catch (error) {
        toast.error("Terjadi kesalahan saat menghapus mahasiswa", {
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

    onBeforeUnmount(() => {
      importModal?.dispose();
    });

    function goAdd() {
      return router.push("/mahasiswa/add");
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
      importing,
      fileInput,
      importModalRef,
      selectedFile,
      isDragging,
      openImportModal,
      closeImportModal,
      triggerFileSelect,
      onFileSelected,
      onDragOver,
      onDragLeave,
      onDrop,
      removeFile,
      formatFileSize,
      downloadTemplate,
      importData,
    };
  },
});
</script>

<template>
  <div class="container-fluid">
    <div
      class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb"
    >
      <h1 class="page-title fw-semibold fs-18 mb-0">Mahasiswa</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);">Master</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Mahasiswa</li>
          </ol>
        </nav>
      </div>
    </div>

    <SimpleCardComponent>
      <template #showheader>
        <div class="d-flex gap-2">
          <button
            class="btn btn-success btn-wave shadow-sm"
            @click="openImportModal"
          >
            <i class="ri-file-excel-2-line align-middle me-1"></i>
            Import Excel
          </button>
          <button class="btn btn-primary btn-wave shadow-sm" @click="goAdd">
            <i class="ri-add-line align-middle me-1"></i> Tambah Data
          </button>
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

    <!-- ==================== IMPORT MODAL ==================== -->
    <div
      class="modal fade"
      id="importExcelModal"
      ref="importModalRef"
      tabindex="-1"
      aria-labelledby="importExcelModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content import-modal-content">
          <!-- Header -->
          <div class="modal-header import-modal-header">
            <div class="d-flex align-items-center gap-3">
              <div class="import-modal-icon">
                <i class="ri-file-excel-2-line"></i>
              </div>
              <div>
                <h5 class="modal-title mb-0" id="importExcelModalLabel">
                  Import Data Mahasiswa
                </h5>
                <small class="text-muted">Upload file Excel untuk import data secara massal</small>
              </div>
            </div>
            <button
              type="button"
              class="btn-close"
              @click="closeImportModal"
              aria-label="Close"
            ></button>
          </div>

          <!-- Body -->
          <div class="modal-body p-4">
            <!-- Step 1: Download Template -->
            <div class="import-step-card mb-4">
              <div class="d-flex align-items-start gap-3">
                <div class="step-number">1</div>
                <div class="flex-grow-1">
                  <h6 class="fw-semibold mb-1">Download Template Excel</h6>
                  <p class="text-muted small mb-3">
                    Unduh template yang sudah disesuaikan dengan format yang benar. Isi data sesuai kolom yang tersedia.
                  </p>
                  <button
                    class="btn btn-outline-success btn-wave btn-sm"
                    @click="downloadTemplate"
                  >
                    <i class="ri-download-2-line me-1"></i>
                    Download Template
                  </button>
                </div>
              </div>
            </div>

            <!-- Template columns info -->
            <div class="template-info-card mb-4">
              <div class="d-flex align-items-center gap-2 mb-3">
                <i class="ri-information-line text-primary fs-18"></i>
                <span class="fw-semibold text-primary">Kolom yang tersedia pada template</span>
              </div>
              <div class="row g-2">
                <div class="col-md-4 col-6" v-for="col in [
                  { name: 'Nomor SK BAN-PT', desc: 'No. SK Akreditasi', required: false },
                  { name: 'Nilai Akreditasi', desc: 'Contoh: Akreditasi B', required: false },
                  { name: 'Nomor Ijazah Nasional', desc: '15 digit nomor', required: false },
                  { name: 'Nama', desc: 'Nama lengkap', required: true },
                  { name: 'Tempat tanggal Lahir', desc: 'Contoh: Kota, DD Bulan YYYY', required: true },
                  { name: 'NIM', desc: 'Nomor Induk Mahasiswa', required: true },
                  { name: 'NIK', desc: '16 digit NIK', required: true },
                  { name: 'Tanggal SK Yudisium', desc: 'Contoh: 10 Januari 2021', required: false },
                  { name: 'Tanggal Penerbitan', desc: 'Contoh: 10 Januari 2021', required: false },
                ]" :key="col.name">
                  <div class="template-col-badge">
                    <span class="fw-semibold">{{ col.name }}</span>
                    <small class="d-block text-muted">{{ col.desc }}</small>
                    <span v-if="col.required" class="badge bg-danger-transparent mt-1" style="font-size: 10px;">Wajib</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Upload File -->
            <div class="import-step-card mb-3">
              <div class="d-flex align-items-start gap-3">
                <div class="step-number">2</div>
                <div class="flex-grow-1">
                  <h6 class="fw-semibold mb-1">Upload File Excel</h6>
                  <p class="text-muted small mb-3">
                    Pilih atau seret file Excel yang sudah diisi ke area di bawah ini.
                  </p>

                  <!-- Dropzone -->
                  <div
                    class="dropzone-area"
                    :class="{ 'dropzone-active': isDragging, 'dropzone-has-file': selectedFile }"
                    @dragover="onDragOver"
                    @dragleave="onDragLeave"
                    @drop="onDrop"
                    @click="triggerFileSelect"
                  >
                    <!-- Empty state -->
                    <div v-if="!selectedFile" class="dropzone-content">
                      <div class="dropzone-icon">
                        <i class="ri-upload-cloud-2-line"></i>
                      </div>
                      <p class="mb-1 fw-semibold">
                        Klik atau seret file ke sini
                      </p>
                      <p class="text-muted small mb-0">
                        Format: .xlsx, .xls, .csv &bull; Maks. 10MB
                      </p>
                    </div>

                    <!-- File selected state -->
                    <div v-else class="dropzone-file-info" @click.stop>
                      <div class="d-flex align-items-center gap-3">
                        <div class="file-icon-box">
                          <i class="ri-file-excel-2-fill"></i>
                        </div>
                        <div class="flex-grow-1 text-start">
                          <p class="mb-0 fw-semibold text-truncate" style="max-width: 350px;">
                            {{ selectedFile.name }}
                          </p>
                          <small class="text-muted">
                            {{ formatFileSize(selectedFile.size) }}
                          </small>
                        </div>
                        <button
                          class="btn btn-sm btn-icon btn-danger-light btn-wave"
                          @click.stop="removeFile"
                          title="Hapus file"
                        >
                          <i class="ri-close-line"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <input
                    ref="fileInput"
                    type="file"
                    accept=".xlsx,.xls,.csv"
                    class="d-none"
                    @change="onFileSelected"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer import-modal-footer">
            <button
              type="button"
              class="btn btn-light btn-wave"
              @click="closeImportModal"
              :disabled="importing"
            >
              Batal
            </button>
            <button
              type="button"
              class="btn btn-primary btn-wave"
              @click="importData"
              :disabled="!selectedFile || importing"
            >
              <span v-if="importing">
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                Mengimport...
              </span>
              <span v-else>
                <i class="ri-upload-2-line me-1"></i>
                Import Data
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- ==================== END IMPORT MODAL ==================== -->
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

/* ===== Import Modal Styles ===== */
.import-modal-content {
  border: none;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.import-modal-header {
  background: linear-gradient(135deg, #198754 0%, #20c997 100%);
  border-bottom: none;
  padding: 1.25rem 1.5rem;
  color: #fff;
}

.import-modal-header .btn-close {
  filter: brightness(0) invert(1);
  opacity: 0.8;
}

.import-modal-header .btn-close:hover {
  opacity: 1;
}

.import-modal-header .text-muted {
  color: rgba(255, 255, 255, 0.8) !important;
}

.import-modal-icon {
  width: 48px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  backdrop-filter: blur(10px);
}

/* Step Card */
.import-step-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1.25rem;
  border: 1px solid #e9ecef;
  transition: all 0.3s ease;
}

.import-step-card:hover {
  border-color: #dee2e6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.step-number {
  width: 32px;
  height: 32px;
  min-width: 32px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

/* Template Info */
.template-info-card {
  background: linear-gradient(135deg, #eff6ff, #f0f9ff);
  border: 1px solid #bfdbfe;
  border-radius: 12px;
  padding: 1.25rem;
}

.template-col-badge {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  text-align: center;
  font-size: 12px;
  transition: all 0.2s ease;
}

.template-col-badge:hover {
  border-color: #6366f1;
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(99, 102, 241, 0.1);
}

/* Dropzone */
.dropzone-area {
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #fafafa;
}

.dropzone-area:hover {
  border-color: #6366f1;
  background: #f5f3ff;
}

.dropzone-active {
  border-color: #6366f1;
  background: #ede9fe;
  transform: scale(1.01);
}

.dropzone-has-file {
  border-style: solid;
  border-color: #198754;
  background: #f0fdf4;
  cursor: default;
}

.dropzone-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #6366f1;
  animation: floatIcon 3s ease-in-out infinite;
}

@keyframes floatIcon {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.file-icon-box {
  width: 48px;
  height: 48px;
  min-width: 48px;
  background: linear-gradient(135deg, #dcfce7, #bbf7d0);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #16a34a;
}

/* Footer */
.import-modal-footer {
  border-top: 1px solid #f1f5f9;
  padding: 1rem 1.5rem;
  background: #fafafa;
}
</style>

