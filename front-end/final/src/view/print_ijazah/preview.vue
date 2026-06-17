<script lang="ts">
import { defineComponent, onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { apiGet } from "../../services/api/request";
import { BASE_URL } from "../../services/api/http";
import router from "../../router";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export default defineComponent({
  setup() {
    const route = useRoute();
    const batchId = route.params.batch_id;
    const batchData = ref<any>(null);
    const ijazahList = ref<any[]>([]);
    const loading = ref(true);
    const baseUrl = BASE_URL.replace('/api', '');

    async function fetchData() {
      try {
        loading.value = true;
        const response = await apiGet(`/print-ijazah/batch/${batchId}`);
        if (response.data) {
          batchData.value = response.data.batch;
          ijazahList.value = response.data.data;
          console.log('PREVIEW: ijazahList loaded', ijazahList.value.length, 'items');
          if (ijazahList.value.length > 0) {
            const first = ijazahList.value[0];
            console.log('PREVIEW: first template:', first.template);
            console.log('PREVIEW: teks_statis:', first.template?.teks_statis);
            console.log('PREVIEW: posisi:', first.template?.posisi);
          }
        }
      } catch (error) {
        toast.error("Gagal mengambil data ijazah", {
          theme: "auto",
          position: "top-right",
          autoClose: true,
        });
      } finally {
        loading.value = false;
      }
    }

    function doPrint() {
      window.print();
    }

    function goBack() {
      router.push('/print-ijazah');
    }

    function getImageUrl(path: string) {
      if (!path) return '';
      if (path.startsWith('http')) return path;
      return `${baseUrl}/storage/${path}`;
    }

    // Get the value of a dynamic field from the mahasiswa data
    function getFieldValue(mahasiswa: any, fieldName: string): string {
      return mahasiswa[fieldName] ?? '';
    }

    // Paper sizes at 96 DPI (for print preview)
    const PAPER_SIZES: Record<string, {w: number, h: number}> = {
      A4:    { w: 794,  h: 1123 },
      A3:    { w: 1123, h: 1587 },
      Legal: { w: 794,  h: 1346 },
      F4:    { w: 794,  h: 1330 },
    };

    // Paper sizes at 72 DPI (editor canvas sizes - must match editor.vue)
    const EDITOR_PAPER_SIZES: Record<string, {w: number, h: number}> = {
      A4:    { w: 595,  h: 842 },
      A3:    { w: 842,  h: 1191 },
      Legal: { w: 612,  h: 1008 },
      F4:    { w: 612,  h: 935 },
    };

    function getPageSize(template: any) {
      const size = PAPER_SIZES[template.ukuran_kertas] || PAPER_SIZES['A4'];
      if (template.orientasi === 'landscape') {
        return { width: size.h, height: size.w };
      }
      return { width: size.w, height: size.h };
    }

    function getEditorSize(template: any) {
      const size = EDITOR_PAPER_SIZES[template.ukuran_kertas] || EDITOR_PAPER_SIZES['A4'];
      if (template.orientasi === 'landscape') {
        return { width: size.h, height: size.w };
      }
      return { width: size.w, height: size.h };
    }

    // Scale factor: editor coordinates (72 DPI) → preview coordinates (96 DPI)
    function getScale(template: any) {
      const editorSize = EDITOR_PAPER_SIZES[template.ukuran_kertas] || EDITOR_PAPER_SIZES['A4'];
      const previewSize = PAPER_SIZES[template.ukuran_kertas] || PAPER_SIZES['A4'];
      if (template.orientasi === 'landscape') {
        return previewSize.h / editorSize.h;
      }
      return previewSize.w / editorSize.w;
    }

    /**
     * Get static text content from saved teks_statis (DB) or fallback to defaults.
     * Data saved through editor → template_ijazah.teks_statis column
     */
    function getStaticContent(template: any): any[] {
      // 1. Check if teks_statis exists as array
      if (template.teks_statis && Array.isArray(template.teks_statis) && template.teks_statis.length > 0) {
        return template.teks_statis;
      }
      // 2. Check if teks_statis is a JSON string
      if (typeof template.teks_statis === 'string') {
        try {
          const parsed = JSON.parse(template.teks_statis);
          if (Array.isArray(parsed) && parsed.length > 0) {
            return parsed;
          }
        } catch (e) {}
      }
      // 3. Return empty array if no static content is found
      return [];
    }

    /**
     * Get dynamic field positions from saved posisi_template (DB) or fallback to defaults.
     * Data saved through editor → posisi_template table
     * Returns normalized array with consistent property names for the template.
     */
    function getDynamicFields(template: any): any[] {
      // If posisi (from posisiTemplate relation) exists and has items, use it
      if (template.posisi && Array.isArray(template.posisi) && template.posisi.length > 0) {
        return template.posisi.map((pos: any) => ({
          field_name: pos.field_name,
          x: Number(pos.posisi_x),
          y: Number(pos.posisi_y),
          fontSize: Number(pos.font_size),
          fontFamily: pos.font_family || 'Arial',
          fontWeight: pos.font_weight || 'normal',
          alignment: pos.alignment || 'left',
          textColor: pos.text_color || '#000000',
        }));
      }

      // Fallback: if fields_positions exists (from template_ijazah.fields_positions column)
      if (template.fields_positions) {
        let positions = template.fields_positions;
        if (typeof positions === 'string') {
          try {
            positions = JSON.parse(positions);
          } catch (e) {
            positions = {};
          }
        }
        if (positions && typeof positions === 'object') {
          return Object.keys(positions).map(key => ({
            field_name: key,
            x: Number(positions[key].x || 0),
            y: Number(positions[key].y || 0),
            fontSize: Number(positions[key].fontSize || 12),
            fontFamily: positions[key].fontFamily || 'Arial',
            fontWeight: positions[key].fontWeight || 'normal',
            alignment: positions[key].alignment || 'left',
            textColor: '#000000',
          }));
        }
      }

      // Return empty array if no dynamic field positions are found
      return [];
    }

    const printOrientation = computed(() => {
      if (ijazahList.value.length > 0 && ijazahList.value[0].template) {
        const tpl = ijazahList.value[0].template;
        return tpl.orientasi === 'landscape' ? 'landscape' : 'portrait';
      }
      return 'portrait';
    });

    onMounted(() => {
      fetchData();
    });

    return {
      router,
      batchData,
      ijazahList,
      loading,
      doPrint,
      goBack,
      getImageUrl,
      getFieldValue,
      getPageSize,
      getEditorSize,
      getScale,
      getStaticContent,
      getDynamicFields,
      printOrientation,
    };
  },
});
</script>

<template>
  <div class="preview-wrap">
    <component is="style" v-if="!loading && ijazahList.length > 0" v-html="`@media print { @page { size: ${printOrientation}; margin: 0; } }`"></component>
    <!-- Toolbar (tidak ikut cetak) -->
    <div class="no-print toolbar-bar">
      <div class="toolbar-left">
        <button class="btn btn-sm btn-light me-2" @click="goBack">
          <i class="ri-arrow-left-line"></i> Kembali
        </button>
        <div>
          <div class="fw-semibold">Batch: {{ batchData?.nama_batch || '...' }}</div>
          <small class="text-muted">{{ ijazahList.length }} Ijazah</small>
        </div>
      </div>
      <button
        class="btn btn-primary"
        @click="doPrint"
        :disabled="loading || ijazahList.length === 0"
      >
        <i class="ri-printer-line me-1"></i> Cetak Ijazah
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="no-print text-center my-5 py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Menyiapkan data ijazah...</p>
    </div>

    <!-- Area Cetak -->
    <div class="print-area" v-if="!loading">
      <template v-for="(item, index) in ijazahList" :key="index">

        <!-- Ijazah dengan Template -->
        <div
          v-if="item.template"
          class="ijazah-page"
          :style="{
            width: getPageSize(item.template).width + 'px',
            height: getPageSize(item.template).height + 'px',
          }"
        >
          <!-- Inner wrapper for exact scale matching -->
          <div
            class="ijazah-inner"
            :style="{
              width: getEditorSize(item.template).width + 'px',
              height: getEditorSize(item.template).height + 'px',
              transform: `scale(${getScale(item.template)})`,
              transformOrigin: 'top left',
              position: 'relative'
            }"
          >
            <!-- Background Image -->
            <img
              v-if="item.template.file_background"
              :src="getImageUrl(item.template.file_background)"
              class="ijazah-bg"
              alt="background"
            />

            <!-- Warning jika template kosong -->
            <div
              v-if="getStaticContent(item.template).length === 0 && getDynamicFields(item.template).length === 0"
              class="no-print position-absolute top-50 start-50 translate-middle w-75 bg-warning bg-opacity-10 border border-warning rounded p-4 text-center"
              style="z-index: 10;"
            >
              <i class="ri-alert-line fs-1 text-warning mb-2 d-block"></i>
              <h5 class="text-warning">Template Belum Dikonfigurasi</h5>
              <p class="text-muted mb-0">Template <strong>{{ item.template.nama_template }}</strong> belum memiliki teks statis maupun posisi field dinamis.</p>
              <p class="text-muted mb-0">Silakan atur template ini melalui menu Editor Template.</p>
            </div>

            <!-- Static Content (dynamic labels from editor) -->
            <div
              v-for="(itemStatic, idx) in getStaticContent(item.template)"
              :key="'static-' + idx"
              class="pos-field"
              :style="{
                position: 'absolute',
                left: itemStatic.x + 'px',
                top: itemStatic.y + 'px',
                fontSize: itemStatic.fontSize + 'px',
                fontFamily: itemStatic.fontFamily,
                fontWeight: itemStatic.fontWeight,
                textAlign: itemStatic.alignment,
                whiteSpace: 'nowrap',
                transform: itemStatic.alignment === 'center' ? 'translateX(-50%)' : (itemStatic.alignment === 'right' ? 'translateX(-100%)' : 'none'),
                padding: '2px 4px',
                color: '#333',
                zIndex: 1
              }"
            >
              {{ itemStatic.text }}
            </div>

            <!-- Photo placeholder -->
            <div
              class="photo-placeholder"
              :style="{
                position: 'absolute',
                left: '50%',
                top: '465px',
                width: '80px',
                height: '110px',
                transform: 'translateX(-50%)',
                border: '2px solid #333',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                backgroundColor: '#fff',
                fontSize: '10px',
                color: '#666',
                zIndex: 1
              }"
            >
              Foto 3x4
            </div>

            <!-- Field-field posisional (Data Dinamis Mahasiswa) -->
            <div
              v-for="(pos, i) in getDynamicFields(item.template)"
              :key="'field-' + i"
              class="pos-field"
              :style="{
                left:       pos.x + 'px',
                top:        pos.y + 'px',
                fontSize:   pos.fontSize + 'px',
                fontFamily: pos.fontFamily,
                fontWeight: pos.fontWeight === 'bold' ? 'bold' : 'normal',
                color:      pos.textColor || '#000000',
                textAlign:  pos.alignment,
                transform:  pos.alignment === 'center' ? 'translateX(-50%)' : (pos.alignment === 'right' ? 'translateX(-100%)' : 'none'),
                padding:    '2px 4px',
                zIndex: 2
              }"
              v-html="getFieldValue(item.mahasiswa, pos.field_name, item.rektor)"
            >
            </div>
          </div>
        </div>

        <!-- Fallback: Template Tidak Ditemukan -->
        <div
          v-else
          class="ijazah-page ijazah-notfound no-print"
        >
          <div class="text-center">
            <i class="ri-error-warning-line ri-3x text-warning mb-3"></i>
            <h5>Template Tidak Ditemukan</h5>
            <p class="text-muted mb-0">
              Mahasiswa: <strong>{{ item.raw_mahasiswa?.nama ?? item.mahasiswa?.nama_mahasiswa }}</strong>
            </p>
            <p class="text-muted">Prodi belum memiliki template aktif yang cocok.</p>
          </div>
        </div>

      </template>
    </div>
  </div>
</template>

<style scoped>
/* ========================
   LAYOUT TOOLBAR
======================== */
.preview-wrap {
  background: #e8e8e8;
  min-height: 100vh;
  padding-bottom: 60px;
}

.toolbar-bar {
  position: sticky;
  top: 0;
  z-index: 100;
  background: #fff;
  border-bottom: 1px solid #ddd;
  padding: 12px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.toolbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* ========================
   AREA CETAK
======================== */
.print-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  padding: 32px 16px;
}

/* ========================
   HALAMAN IJAZAH
======================== */
.ijazah-page {
  position: relative;
  background: #fff;
  box-shadow: 0 4px 24px rgba(0,0,0,0.18);
  overflow: hidden;
  /* Ukuran diset via inline style per item */
}

.ijazah-notfound {
  width: 794px;
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px dashed #ffc107;
  background: #fffbf0 !important;
}

.ijazah-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  object-fit: fill; /* fill agar pas dengan ukuran kertas */
  display: block;
}

.pos-field {
  position: absolute;
  z-index: 2;
  white-space: nowrap;
}

/* ========================
   PRINT MODE
======================== */
@media print {
  @page {
    margin: 0;
    padding: 0;
  }

  body, html {
    margin: 0 !important;
    padding: 0 !important;
  }

  .no-print {
    display: none !important;
  }

  .preview-wrap {
    background: transparent !important;
    padding: 0 !important;
    min-height: unset;
  }

  .print-area {
    gap: 0 !important;
    padding: 0 !important;
    display: block;
  }

  .ijazah-page {
    box-shadow: none !important;
    page-break-after: always;
    page-break-inside: avoid;
    margin: 0 !important;
    position: relative;
  }

  .ijazah-page:last-child {
    page-break-after: auto;
  }
}
</style>
