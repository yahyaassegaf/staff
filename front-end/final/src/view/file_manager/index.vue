<script lang="ts">
import { defineComponent, onMounted, onUnmounted, ref, computed, watch } from "vue";
import { apiGet, apiPdf } from "../../services/api/request";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default defineComponent({
  name: "FileManagerView",
  setup() {
    const folders = ref<any[]>([]);
    const files = ref<any[]>([]);
    const currentPath = ref("");
    const userProdi = ref("");
    const searchQuery = ref("");
    const loading = ref(false);
    
    // State untuk melacak kebab menu folder mana yang sedang aktif/terbuka
    const activeDropdown = ref<string | null>(null);

    // State untuk Pagination
    const currentPage = ref(1);
    const itemsPerPage = 10;

    // Mengambil daftar isi folder fisik dari backend
    const loadFolder = async (path: string = "") => {
      try {
        loading.value = true;
        const response = await apiGet("/file-manager/list", { path });
        if (response.success && response.data.status) {
          folders.value = response.data.data.folders || [];
          files.value = response.data.data.files || [];
          currentPath.value = response.data.data.current_path || "";
          userProdi.value = response.data.data.user_prodi || "";
        } else {
          toast.error(response.error || "Gagal mengambil daftar berkas.");
        }
      } catch (err: any) {
        toast.error("Terjadi kesalahan saat memuat berkas.");
      } finally {
        loading.value = false;
        activeDropdown.value = null; // Reset dropdown saat berpindah folder
      }
    };

    // Filter folder berdasarkan pencarian
    const filteredFolders = computed(() => {
      if (!searchQuery.value.trim()) return folders.value;
      return folders.value.filter(folder => 
        folder.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    // Filter file berdasarkan pencarian
    const filteredFiles = computed(() => {
      if (!searchQuery.value.trim()) return files.value;
      return files.value.filter(file => 
        file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    // Menghitung seluruh data setelah difilter pencarian (folder + file)
    const allItems = computed(() => {
      return [...filteredFolders.value, ...filteredFiles.value];
    });

    // Membagi data berdasarkan halaman aktif (10 per halaman)
    const paginatedItems = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage;
      const end = start + itemsPerPage;
      return allItems.value.slice(start, end);
    });

    // Folder yang termasuk di halaman saat ini
    const paginatedFolders = computed(() => {
      return paginatedItems.value.filter(item => item.type === 'folder');
    });

    // File yang termasuk di halaman saat ini
    const paginatedFiles = computed(() => {
      return paginatedItems.value.filter(item => item.type === 'file');
    });

    // Total halaman yang tersedia
    const totalPages = computed(() => {
      return Math.ceil(allItems.value.length / itemsPerPage);
    });

    // Reset ke halaman 1 ketika user berpindah folder atau mengetik pencarian
    watch(currentPath, () => {
      currentPage.value = 1;
    });

    watch(searchQuery, () => {
      currentPage.value = 1;
    });

    // Membuat breadcrumb interaktif secara dinamis berdasarkan path saat ini
    const breadcrumbs = computed(() => {
      if (!currentPath.value) return [];
      const parts = currentPath.value.split("/");
      let accumulatedPath = "";
      return parts.map((part) => {
        accumulatedPath = accumulatedPath ? `${accumulatedPath}/${part}` : part;
        return {
          name: part,
          path: accumulatedPath
        };
      });
    });

    const navigateTo = (path: string) => {
      loadFolder(path);
    };

    const goBack = () => {
      if (!currentPath.value) return;
      const parts = currentPath.value.split("/");
      parts.pop();
      navigateTo(parts.join("/"));
    };

    // Penanganan klik pada file dinamis
    const openFile = (file: any) => {
      if (file.drive_link) {
        // Jika terdapat drive_link di database, buka link Google Drive di tab baru
        window.open(file.drive_link, "_blank");
      } else if (file.url) {
        // Jika kosong, buka file lokal
        window.open(file.url, "_blank");
      } else {
        toast.error("Link berkas tidak tersedia.");
      }
    };

    // Mengompresi folder target dan memicu unduhan ZIP secara aman
    const downloadFolderZip = async (folderPath: string) => {
      const toastId = toast.loading("Sedang menyiapkan berkas ZIP, mohon tunggu...");
      try {
        const response = await apiPdf("/file-manager/download-zip", { path: folderPath });
        
        // Konversi data response menjadi Blob ZIP
        const blob = new Blob([response.data], { type: "application/zip" });
        
        // Ambil nama dasar folder untuk penamaan ZIP
        const folderName = folderPath.split("/").pop() || "download";
        
        // Trigger unduh berkas di browser
        const link = document.createElement("a");
        link.href = window.URL.createObjectURL(blob);
        link.download = `${folderName}.zip`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        toast.update(toastId, { 
          render: "Folder berhasil diunduh sebagai ZIP!", 
          type: "success", 
          isLoading: false, 
          autoClose: 3000 
        });
      } catch (err: any) {
        toast.update(toastId, { 
          render: "Gagal mengunduh folder sebagai ZIP.", 
          type: "error", 
          isLoading: false, 
          autoClose: 3000 
        });
      }
    };

    // Fungsi untuk membuka/menutup dropdown kebab menu folder secara individual
    const toggleDropdown = (path: string, event: Event) => {
      event.stopPropagation(); // Mencegah navigasi masuk folder
      if (activeDropdown.value === path) {
        activeDropdown.value = null;
      } else {
        activeDropdown.value = path;
      }
    };

    // Menutup dropdown jika user mengklik di luar menu
    const closeAllDropdowns = () => {
      activeDropdown.value = null;
    };

    onMounted(() => {
      loadFolder("");
      window.addEventListener("click", closeAllDropdowns);
    });

    onUnmounted(() => {
      window.removeEventListener("click", closeAllDropdowns);
    });

    return {
      folders,
      files,
      currentPath,
      userProdi,
      searchQuery,
      loading,
      filteredFolders,
      filteredFiles,
      breadcrumbs,
      navigateTo,
      goBack,
      openFile,
      downloadFolderZip,
      activeDropdown,
      toggleDropdown,
      currentPage,
      itemsPerPage,
      allItems,
      paginatedFolders,
      paginatedFiles,
      totalPages,
    };
  }
});
</script>

<template>
  <div class="container-fluid">
    <!-- Header & Breadcrumbs -->
    <div class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb">
      <h1 class="page-title fw-semibold fs-18 mb-0">File Manager</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item">
              <a href="javascript:void(0);" @click="navigateTo('')" class="text-primary fw-medium">All Files</a>
            </li>
            <li v-for="(crumb, idx) in breadcrumbs" :key="idx" class="breadcrumb-item" :class="{ active: idx === breadcrumbs.length - 1 }">
              <a v-if="idx < breadcrumbs.length - 1" href="javascript:void(0);" @click="navigateTo(crumb.path)" class="text-primary">{{ crumb.name }}</a>
              <span v-else class="text-muted">{{ crumb.name }}</span>
            </li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Kotak Utama File Manager -->
    <div class="card custom-card">
      <div class="card-body">
        
        <!-- Toolbar & Search -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
          <div class="d-flex align-items-center gap-2">
            <button v-if="currentPath" @click="goBack" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1 shadow-sm">
              <i class="ri-arrow-left-line"></i> Kembali
            </button>
            <span class="text-muted fs-13 d-inline-flex align-items-center gap-1 ms-2" v-if="userProdi">
              Fakultas / Prodi: <span class="badge bg-primary-transparent rounded-pill fs-11 py-1 px-2">{{ userProdi }}</span>
            </span>
          </div>

          <!-- Search Bar -->
          <div class="col-md-3 col-sm-6">
            <div class="input-group input-group-merge shadow-sm">
              <input type="text" class="form-control form-control-sm" v-model="searchQuery" placeholder="Cari folder atau file..." />
              <button class="btn btn-primary btn-sm" type="button">
                <i class="ri-search-line"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Animasi Spinner Loading -->
        <div v-if="loading" class="row">
          <div class="col-12 text-center my-5 py-5">
            <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted fw-medium fs-13">Memuat direktori berkas...</p>
          </div>
        </div>

        <!-- Tampilan Jika Kosong -->
        <div v-else-if="allItems.length === 0" class="text-center my-5 py-5">
          <i class="ri-folder-open-line text-muted display-3 d-block mb-3"></i>
          <h5 class="text-dark fw-semibold fs-16">Folder ini kosong</h5>
          <p class="text-muted fs-13 max-w-350 mx-auto">Tidak ada direktori folder atau berkas surat PDF di dalam folder ini saat ini.</p>
        </div>

        <!-- Grid Berkas -->
        <div v-else>
          <!-- Bagian Folder -->
          <div v-if="paginatedFolders.length > 0" class="mb-4">
            <h6 class="fw-bold mb-3 text-uppercase text-muted fs-11 tracking-wider">Direktori Folder</h6>
            <div class="row g-3">
              <div v-for="folder in paginatedFolders" :key="folder.path" class="col-xxl-3 col-xl-4 col-md-6 col-sm-12" :style="activeDropdown === folder.path ? 'z-index: 1050; position: relative;' : ''">
                <div class="card folder-card h-100 border shadow-sm" :style="activeDropdown === folder.path ? 'overflow: visible !important;' : ''">
                  <div class="card-body p-3 d-flex align-items-center justify-content-between" style="overflow: visible !important;">
                    <div class="d-flex align-items-center gap-3 clickable-area cursor-pointer flex-grow-1 text-truncate" @click="navigateTo(folder.path)">
                      <div class="folder-icon-wrapper text-warning">
                        <i class="ri-folder-5-fill fs-28"></i>
                      </div>
                      <div class="text-truncate">
                        <span class="fw-semibold text-dark fs-13 d-block text-truncate" :title="folder.name">{{ folder.name }}</span>
                        <span class="text-muted fs-11">Folder</span>
                      </div>
                    </div>

                     <!-- Dropdown Menu Kebab -->
                    <div class="dropdown position-relative">
                      <button class="btn btn-sm btn-icon btn-light rounded-circle" type="button" @click.stop="toggleDropdown(folder.path, $event)">
                        <i class="ri-more-2-fill fs-14"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end shadow border" :style="activeDropdown === folder.path ? 'display: block; position: absolute; right: 0; top: 100%; z-index: 1050; min-width: 10rem;' : 'display: none;'">
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="javascript:void(0);" @click.stop="navigateTo(folder.path)">
                            <i class="ri-folder-open-line text-muted fs-15"></i> Buka Folder
                          </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-primary" href="javascript:void(0);" @click.stop="downloadFolderZip(folder.path)">
                            <i class="ri-file-zip-line fs-15"></i> Download ZIP
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Bagian File PDF -->
          <div v-if="paginatedFiles.length > 0">
            <h6 class="fw-bold mb-3 text-uppercase text-muted fs-11 tracking-wider">Berkas PDF</h6>
            <div class="row g-3">
              <div v-for="file in paginatedFiles" :key="file.path" class="col-xxl-3 col-xl-4 col-md-6 col-sm-12">
                <div class="card file-card h-100 border shadow-sm cursor-pointer" @click="openFile(file)">
                  <div class="card-body p-3 d-flex align-items-start gap-3">
                    <div class="file-icon-wrapper text-danger mt-1">
                      <i class="ri-file-pdf-2-fill fs-36"></i>
                    </div>
                    <div class="flex-grow-1 text-truncate">
                      <span class="fw-semibold text-dark fs-12 d-block text-truncate" :title="file.name">{{ file.name }}</span>
                      <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-muted fs-11">{{ file.size }}</span>
                        
                        <!-- Status Google Drive vs Lokal -->
                        <span v-if="file.drive_link" class="badge bg-success-transparent rounded-pill fs-9 py-1 px-2 d-inline-flex align-items-center gap-1 fw-semibold">
                          <i class="ri-google-fill fs-10"></i> Drive
                        </span>
                        <span v-else class="badge bg-light text-muted rounded-pill fs-9 py-1 px-2 fw-semibold border">
                          Lokal
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Kontrol Pagination -->
          <div v-if="totalPages > 1" class="d-flex align-items-center justify-content-between mt-4 border-top pt-3 flex-wrap gap-2">
            <span class="text-muted fs-13">
              Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, allItems.length) }} dari {{ allItems.length }} item
            </span>
            <nav aria-label="Page navigation">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <a class="page-link" href="javascript:void(0);" @click.prevent="currentPage > 1 && (currentPage--)">
                    <i class="ri-arrow-left-s-line align-middle"></i> Prev
                  </a>
                </li>
                <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
                  <a class="page-link" href="javascript:void(0);" @click.prevent="currentPage = page">{{ page }}</a>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <a class="page-link" href="javascript:void(0);" @click.prevent="currentPage < totalPages && (currentPage++)">
                    Next <i class="ri-arrow-right-s-line align-middle"></i>
                  </a>
                </li>
              </ul>
            </nav>
          </div>

        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
.folder-card, .file-card {
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  border-radius: 8px;
  background: var(--bs-card-bg);
}
.folder-card {
  overflow: visible !important;
}
.folder-card:hover, .file-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04) !important;
  border-color: var(--bs-primary) !important;
}
.folder-icon-wrapper, .file-icon-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.tracking-wider {
  letter-spacing: 0.08em;
}
.cursor-pointer {
  cursor: pointer;
}
.btn-icon {
  width: 1.85rem;
  height: 1.85rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.max-w-350 {
  max-width: 350px;
}
.dropdown-item {
  font-size: 13px;
}
.dropdown-item:active {
  background-color: var(--bs-primary);
}
</style>
