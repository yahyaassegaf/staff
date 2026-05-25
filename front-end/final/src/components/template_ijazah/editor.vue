<script lang="ts" setup>
import { reactive, watch, ref, onMounted, computed } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import { apiGet } from "../../services/api/request";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const defaultForm = {
  id: "",
  prodi_id: null as number | null,
  jenjang: "",
  nama_template: "",
  file_background: "",
  ukuran_kertas: "A4",
  orientasi: "portrait",
  is_active: "aktif",
  fields_positions: {} as any,
};

const form = reactive({ ...defaultForm });
const isLoadingData = ref(false);
const prodiOptions = ref<any[]>([]);

// Static content (labels/text that don't change) - rendered on canvas but not draggable
const staticContent = ref([
  // Top-left header info labels
  { text: 'Nomor Ijazah Nasional', x: 30, y: 30, fontSize: 8, fontFamily: 'Arial', fontWeight: 'normal', alignment: 'left' },
  { text: ':', x: 125, y: 30, fontSize: 8, fontFamily: 'Arial', fontWeight: 'normal', alignment: 'left' },
  { text: 'Nomor SK BAN-PT', x: 30, y: 42, fontSize: 8, fontFamily: 'Arial', fontWeight: 'normal', alignment: 'left' },
  { text: ':', x: 125, y: 42, fontSize: 8, fontFamily: 'Arial', fontWeight: 'normal', alignment: 'left' },
  { text: 'Nilai Akreditasi', x: 30, y: 54, fontSize: 8, fontFamily: 'Arial', fontWeight: 'normal', alignment: 'left' },
  { text: ':', x: 125, y: 54, fontSize: 8, fontFamily: 'Arial', fontWeight: 'normal', alignment: 'left' },

  // Center header
  { text: 'KEMENTERIAN AGAMA REPUBLIK INDONESIA', x: 300, y: 100, fontSize: 11, fontFamily: 'Times New Roman', fontWeight: 'bold', alignment: 'center', isPageCenter: true },
  { text: 'INSTITUT AGAMA ISLAM DARULLUGHAH WADDA\'WAH', x: 300, y: 116, fontSize: 12, fontFamily: 'Times New Roman Bold', fontWeight: 'bold', alignment: 'center', isPageCenter: true },
  { text: 'BANGIL', x: 300, y: 132, fontSize: 12, fontFamily: 'Times New Roman Bold', fontWeight: 'bold', alignment: 'center', isPageCenter: true },

  // Body text
  { text: 'dengan ini menyatakan bahwa:', x: 300, y: 170, fontSize: 10, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'center', isPageCenter: true },

  // Labels before dynamic fields
  { text: 'lahir di', x: 350, y: 222, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'left' },
  { text: 'NIM:', x: 370, y: 240, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'left' },
  { text: 'NIK:', x: 370, y: 255, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'left' },

  { text: 'telah menyelesaikan dengan baik dan memenuhi segala syarat pendidikan', x: 300, y: 290, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'center', isPageCenter: true },
  { text: 'pada tanggal', x: 420, y: 305, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'left' },
  { text: 'oleh sebab itu kepadanya diberikan ijazah dan gelar:', x: 300, y: 320, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'center', isPageCenter: true },

  { text: 'beserta hak dan kewajiban yang melekat pada gelar tersebut.', x: 300, y: 370, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'center', isPageCenter: true },

  // Signature labels
  { text: 'Rektor,', x: 100, y: 470, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'center' },
  { text: 'Dekan,', x: 620, y: 470, fontSize: 9, fontFamily: 'Times New Roman', fontWeight: 'normal', alignment: 'center' },
]);

// Define ijazah fields (dynamic/draggable placeholders) based on the image
const ijazahFields = ref([
  // Top-left: nomor-nomor
  { key: 'nomor_ijazah_nasional', label: 'Nomor Ijazah Nasional', x: 135, y: 30, fontSize: 8, fontFamily: 'Arial', alignment: 'left' },
  { key: 'nomor_sk_ban_pt', label: 'Nomor SK BAN-PT', x: 135, y: 42, fontSize: 8, fontFamily: 'Arial', alignment: 'left' },
  { key: 'nilai_akreditasi', label: 'Nilai Akreditasi', x: 135, y: 54, fontSize: 8, fontFamily: 'Arial', alignment: 'left' },

  // Nama mahasiswa (bold, center)
  { key: 'nama_mahasiswa', label: 'Nama Mahasiswa', x: 300, y: 190, fontSize: 13, fontFamily: 'Times New Roman Bold', alignment: 'center' },

  // Tempat & tanggal lahir
  { key: 'tempat_tanggal_lahir', label: 'Tempat & Tanggal Lahir', x: 300, y: 222, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },

  // NIM & NIK
  { key: 'nim', label: 'NIM', x: 300, y: 240, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },
  { key: 'nik', label: 'NIK', x: 300, y: 254, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },

  // Program Studi & Fakultas (bold) - combined on one line in the image
  { key: 'program_studi', label: 'Program Studi', x: 300, y: 305, fontSize: 10, fontFamily: 'Times New Roman Bold', alignment: 'center' },
  { key: 'fakultas', label: 'Fakultas', x: 300, y: 319, fontSize: 10, fontFamily: 'Times New Roman Bold', alignment: 'center' },
  { key: 'tanggal_kelulusan', label: 'Tanggal Kelulusan', x: 390, y: 333, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'left' },

  // Gelar (bold, larger font, underline-style)
  { key: 'gelar', label: 'Gelar', x: 300, y: 370, fontSize: 12, fontFamily: 'Times New Roman Bold', alignment: 'center' },

  // Tanggal & Kota ijazah (right side) - "Bangil, 10 Januari 2021" format
  { key: 'kota_tempat', label: 'Kota Tempat', x: 520, y: 440, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },
  { key: 'tanggal_ijazah', label: 'Tanggal Ijazah', x: 520, y: 454, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },

  // Pejabat penandatangan
  { key: 'nama_rektor', label: 'Nama Rektor', x: 100, y: 530, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },
  { key: 'nidn_rektor', label: 'NIDN Rektor', x: 100, y: 544, fontSize: 8, fontFamily: 'Times New Roman', alignment: 'center' },
  { key: 'nama_dekan', label: 'Nama Dekan', x: 520, y: 530, fontSize: 9, fontFamily: 'Times New Roman', alignment: 'center' },
  { key: 'nidn_dekan', label: 'NIDN Dekan', x: 520, y: 544, fontSize: 8, fontFamily: 'Times New Roman', alignment: 'center' },
]);

const selectedField = ref<any>(null);
const isDragging = ref(false);
const dragOffset = ref({ x: 0, y: 0 });
const canvasRef = ref<HTMLDivElement | null>(null);

// Load prodi options
const loadProdi = async () => {
  try {
    const response = await apiGet('/get-all-prodi');
    if (response.success) {
      const data = response.data?.data || [];
      prodiOptions.value = Array.isArray(data) ? data : [];
    }
  } catch (error) {
  }
};

watch(
  () => props.modelValue,
  async (val) => {
    if (props.isEdit && val) {
      if (!val.nama_template) {
        isLoadingData.value = true;
        return;
      }

      isLoadingData.value = true;
      // Simulate loading
      await new Promise((resolve) => setTimeout(resolve, 500));

      // Reset form then assign
      Object.assign(form, defaultForm);
      Object.assign(form, val);

      // Ensure form.prodi_id is properly set
      if (form.prodi_id) {
        form.prodi_id = Number(form.prodi_id);
      }

      // Load fields positions from saved data
      if (form.fields_positions && typeof form.fields_positions === 'object') {
        Object.keys(form.fields_positions).forEach(key => {
          const fieldIndex = ijazahFields.value.findIndex(f => f.key === key);
          if (fieldIndex !== -1) {
            Object.assign(ijazahFields.value[fieldIndex], form.fields_positions[key]);
          }
        });
      }

      isLoadingData.value = false;
    }
  },
  { immediate: true }
);

const emit = defineEmits(["submit"]);

function submitForm() {
  // Save current positions
  const positions: any = {};
  ijazahFields.value.forEach(field => {
    positions[field.key] = {
      x: field.x,
      y: field.y,
      fontSize: field.fontSize,
      fontFamily: field.fontFamily,
      alignment: field.alignment,
    };
  });
  form.fields_positions = positions;
  emit("submit", form);
}

function selectField(field: any) {
  selectedField.value = field;
}

function onFieldMouseDown(event: MouseEvent, field: any) {
  if (!canvasRef.value) return;
  
  event.preventDefault();
  isDragging.value = true;
  selectedField.value = field;
  
  const rect = canvasRef.value.getBoundingClientRect();
  dragOffset.value = {
    x: event.clientX - rect.left - field.x,
    y: event.clientY - rect.top - field.y,
  };
}

function onCanvasMouseMove(event: MouseEvent) {
  if (!isDragging.value || !selectedField.value || !canvasRef.value) return;
  
  const rect = canvasRef.value.getBoundingClientRect();
  const newX = event.clientX - rect.left - dragOffset.value.x;
  const newY = event.clientY - rect.top - dragOffset.value.y;
  
  // Constrain to canvas bounds
  selectedField.value.x = Math.max(0, Math.min(newX, rect.width - 50));
  selectedField.value.y = Math.max(0, Math.min(newY, rect.height - 20));
}

function onCanvasMouseUp() {
  isDragging.value = false;
}

function updateFieldProperty(property: string, value: any) {
  if (selectedField.value) {
    (selectedField.value as any)[property] = value;
  }
}

function copyPosisiCode() {
  const fieldData = ijazahFields.value.map(f => {
    return `  { key: '${f.key}', label: '${f.label}', x: ${Math.round(f.x)}, y: ${Math.round(f.y)}, fontSize: ${f.fontSize}, fontFamily: '${f.fontFamily}', alignment: '${f.alignment}' }`;
  });
  const codeToPaste = `const ijazahFields = ref([\n${fieldData.join(",\n")}\n]);`;
  
  navigator.clipboard.writeText(codeToPaste)
    .then(() => alert("Berhasil disalin! Buka file editor.vue dan paste (replace) pada variabel const ijazahFields"))
    .catch(() => alert("Console: Cek browser untuk copy manual"));
}

function fieldTag(key: string) {
  return '{{' + key + '}}';
}

onMounted(() => {
  loadProdi();
});

// Canvas dimensions based on 72 DPI paper sizes
const paperDimensions = {
  'A4': { w: 595, h: 842 },
  'A3': { w: 842, h: 1191 },
  'Legal': { w: 612, h: 1008 },
  'F4': { w: 612, h: 935 } // Approx F4 size at 72dpi
};

const canvasWidth = computed(() => {
  const dim = paperDimensions[form.ukuran_kertas] || paperDimensions['A4'];
  return form.orientasi === 'landscape' ? dim.h : dim.w;
});

const canvasHeight = computed(() => {
  const dim = paperDimensions[form.ukuran_kertas] || paperDimensions['A4'];
  return form.orientasi === 'landscape' ? dim.w : dim.h;
});
</script>

<template>
  <div class="row">
    <div class="col-xl-12">
      <div class="card custom-card">
        <div class="card-header">
          <div class="card-title">
            {{ isEdit ? "Edit" : "Tambah" }} Template Ijazah - Editor Posisi
          </div>
        </div>
        <div class="card-body">
          <!-- Template Settings -->
          <div class="row gy-3 mb-4">
            <!-- Template Name -->
            <div class="col-xl-12">
              <label for="input-nama-template" class="form-label">Nama Template :</label>
              <input type="hidden" v-if="isEdit" v-model="form.id" />
              <div v-if="isLoadingData" class="skeleton-input"></div>
              <input
                v-else
                type="text"
                v-model="form.nama_template"
                class="form-control"
                :class="{ 'is-invalid': errors?.nama_template }"
                id="input-nama-template"
                placeholder="Masukkan nama template"
              />
              <div v-if="errors?.nama_template" class="invalid-feedback">
                {{ errors.nama_template[0] }}
              </div>
            </div>

            <!-- Prodi -->
            <div class="col-xl-6">
              <label for="input-prodi" class="form-label">Program Studi :</label>
              <div v-if="isLoadingData" class="skeleton-input"></div>
              <Multiselect
                v-else
                v-model="form.prodi_id"
                :options="prodiOptions"
                :multiple="false"
                :close-on-select="true"
                :clear-on-select="true"
                placeholder="Pilih prodi (kosongkan untuk global)"
                label="nama"
                track-by="id"
                :class="{ 'is-invalid': errors?.prodi_id }"
              ></Multiselect>
              <div v-if="errors?.prodi_id" class="text-danger small mt-1">
                {{ errors.prodi_id[0] }}
              </div>
            </div>

            <!-- Jenjang -->
            <div class="col-xl-6">
              <label for="input-jenjang" class="form-label">Jenjang :</label>
              <div v-if="isLoadingData" class="skeleton-input"></div>
              <select
                v-else
                class="form-select"
                :class="{ 'is-invalid': errors?.jenjang }"
                v-model="form.jenjang"
                id="input-jenjang"
              >
                <option value="">Semua Jenjang</option>
                <option value="D3">D3</option>
                <option value="D4">D4</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
              </select>
              <div v-if="errors?.jenjang" class="invalid-feedback">
                {{ errors.jenjang[0] }}
              </div>
            </div>

            <!-- Ukuran Kertas -->
            <div class="col-xl-4">
              <label for="input-ukuran-kertas" class="form-label">Ukuran Kertas :</label>
              <select
                v-model="form.ukuran_kertas"
                class="form-select"
                :class="{ 'is-invalid': errors?.ukuran_kertas }"
                id="input-ukuran-kertas"
              >
                <option value="A4">A4</option>
                <option value="A3">A3</option>
                <option value="Legal">Legal</option>
                <option value="F4">F4</option>
              </select>
            </div>

            <!-- Orientasi -->
            <div class="col-xl-4">
              <label for="input-orientasi" class="form-label">Orientasi :</label>
              <select
                v-model="form.orientasi"
                class="form-select"
                :class="{ 'is-invalid': errors?.orientasi }"
                id="input-orientasi"
              >
                <option value="portrait">Portrait</option>
                <option value="landscape">Landscape</option>
              </select>
            </div>

            <!-- Status -->
            <div class="col-xl-4">
              <label for="input-is-active" class="form-label">Status :</label>
              <select
                v-model="form.is_active"
                class="form-select"
                :class="{ 'is-invalid': errors?.is_active }"
                id="input-is-active"
              >
                <option value="aktif">Aktif</option>
                <option value="tidak">Tidak Aktif</option>
              </select>
            </div>
          </div>

          <hr />

          <!-- Visual Editor -->
          <div class="row">
            <!-- Field List -->
            <div class="col-xl-3">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Field Ijazah</div>
                </div>
                <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                  <div
                    v-for="field in ijazahFields"
                    :key="field.key"
                    class="field-item p-2 mb-2 border rounded"
                    :class="{ 'field-selected': selectedField?.key === field.key }"
                    @click="selectField(field)"
                    style="cursor: pointer;"
                  >
                    <div class="fw-bold small">{{ field.label }}</div>
                    <div class="text-primary" style="font-size: 11px;">{{ fieldTag(field.key) }}</div>
                    <div class="text-muted" style="font-size: 10px;">
                      X: {{ Math.round(field.x) }}, Y: {{ Math.round(field.y) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Canvas -->
            <div class="col-xl-6">
              <div class="card">
                <div class="card-header">
                  <div class="card-title">Preview Template</div>
                </div>
                <div class="card-body">
                  <div
                    ref="canvasRef"
                    class="ijazah-canvas"
                    :style="{
                      width: canvasWidth + 'px',
                      height: canvasHeight + 'px',
                      position: 'relative',
                      border: '1px solid #ddd',
                      backgroundColor: '#f8f9fa',
                      margin: '0 auto',
                      overflow: 'hidden',
                      backgroundImage: form.file_background ? 'url(' + form.file_background + ')' : 'none',
                      backgroundSize: '100% 100%',
                      backgroundRepeat: 'no-repeat',
                      backgroundPosition: 'center'
                    }"
                    @mousemove="onCanvasMouseMove"
                    @mouseup="onCanvasMouseUp"
                    @mouseleave="onCanvasMouseUp"
                  >
                    <!-- Static Content (non-draggable labels) -->
                    <div
                      v-for="(item, idx) in staticContent"
                      :key="'static-' + idx"
                      class="static-content"
                      :style="{
                        position: 'absolute',
                        left: item.isPageCenter ? '50%' : item.x + 'px',
                        top: item.y + 'px',
                        fontSize: item.fontSize + 'px',
                        fontFamily: item.fontFamily,
                        fontWeight: item.fontWeight,
                        textAlign: item.alignment,
                        whiteSpace: 'nowrap',
                        transform: (item.alignment === 'center' || item.isPageCenter) ? 'translateX(-50%)' : 'none',
                        color: '#333',
                        pointerEvents: 'none',
                        userSelect: 'none',
                        zIndex: 0
                      }"
                    >
                      {{ item.text }}
                    </div>

                    <!-- Draggable Fields -->
                    <div
                      v-for="field in ijazahFields"
                      :key="field.key"
                      class="draggable-field"
                      :class="{ 'field-active': selectedField?.key === field.key }"
                      :style="{
                        position: 'absolute',
                        left: field.x + 'px',
                        top: field.y + 'px',
                        fontSize: field.fontSize + 'px',
                        fontFamily: field.fontFamily,
                        textAlign: field.alignment,
                        transform: field.alignment === 'center' ? 'translateX(-50%)' : 'none',
                        cursor: isDragging ? 'move' : 'pointer',
                        padding: '2px 4px',
                        border: selectedField?.key === field.key ? '2px solid #0d6efd' : '1px dashed rgba(13, 110, 253, 0.4)',
                        borderRadius: '3px',
                        whiteSpace: 'nowrap',
                        zIndex: selectedField?.key === field.key ? 10 : 1,
                        background: selectedField?.key === field.key ? 'rgba(13, 110, 253, 0.15)' : 'rgba(255, 255, 255, 0.5)',
                        boxShadow: selectedField?.key === field.key ? '0 0 0 2px #0d6efd' : 'none'
                      }"
                      @mousedown="onFieldMouseDown($event, field)"
                      @click="selectField(field)"
                    >
                      {{ fieldTag(field.key) }}
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
                        backgroundColor: '#f0f0f0',
                        fontSize: '10px',
                        color: '#666'
                      }"
                    >
                      Foto 3x4
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Properties Panel -->
            <div class="col-xl-3">
              <div class="card" v-if="selectedField">
                <div class="card-header">
                  <div class="card-title">Properti Field</div>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label small">Nama Field</label>
                    <input
                      type="text"
                      class="form-control form-control-sm"
                      :value="selectedField.label"
                      readonly
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label small">Posisi X</label>
                    <input
                      type="number"
                      class="form-control form-control-sm"
                      v-model.number="selectedField.x"
                      @input="updateFieldProperty('x', selectedField.x)"
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label small">Posisi Y</label>
                    <input
                      type="number"
                      class="form-control form-control-sm"
                      v-model.number="selectedField.y"
                      @input="updateFieldProperty('y', selectedField.y)"
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label small">Font Size (px)</label>
                    <input
                      type="number"
                      class="form-control form-control-sm"
                      v-model.number="selectedField.fontSize"
                      @input="updateFieldProperty('fontSize', selectedField.fontSize)"
                      min="8"
                      max="24"
                    />
                  </div>

                  <div class="mb-3">
                    <label class="form-label small">Font Family</label>
                    <select
                      class="form-select form-select-sm"
                      v-model="selectedField.fontFamily"
                      @change="updateFieldProperty('fontFamily', selectedField.fontFamily)"
                    >
                      <option value="Arial">Arial</option>
                      <option value="Arial Bold">Arial Bold</option>
                      <option value="Times New Roman">Times New Roman</option>
                      <option value="Times New Roman Bold">Times New Roman Bold</option>
                      <option value="Calibri">Calibri</option>
                      <option value="Calibri Bold">Calibri Bold</option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label class="form-label small">Alignment</label>
                    <select
                      class="form-select form-select-sm"
                      v-model="selectedField.alignment"
                      @change="updateFieldProperty('alignment', selectedField.alignment)"
                    >
                      <option value="left">Left</option>
                      <option value="center">Center</option>
                      <option value="right">Right</option>
                    </select>
                  </div>

                  <div class="mb-3 mt-4 d-grid">
                    <button class="btn btn-warning btn-sm btn-wave" @click="copyPosisiCode">
                      <i class="ri-clipboard-line me-1"></i> Salin Update (Bantu Code)
                    </button>
                  </div>
                </div>
              </div>

              <div class="card" v-else>
                <div class="card-body text-center text-muted py-5">
                  <i class="ri-click-2-line fs-1"></i>
                  <p class="mt-2 mb-0 small">Pilih field untuk mengedit properti</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn btn-primary-light btn-wave ms-auto float-end" @click="submitForm">
            <i class="ri-save-line me-1"></i>
            {{ isEdit ? "Update" : "Simpan" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.skeleton-input {
  height: 38px;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 5px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.field-item:hover {
  background-color: #f8f9fa;
}

.field-selected {
  background-color: #e7f1ff;
  border-color: #0d6efd !important;
}

.field-active {
  box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}

.draggable-field:hover {
  border-color: #0d6efd !important;
}

.draggable-field:active {
  cursor: grabbing !important;
}

.ijazah-canvas {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  background-color: #fff;
}

.static-content {
  pointer-events: none;
  user-select: none;
  color: #333;
  line-height: 1.3;
}

.photo-placeholder {
  font-size: 10px;
  text-align: center;
  line-height: 1.2;
}
</style>
