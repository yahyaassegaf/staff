<script lang="ts" setup>
import { reactive, ref, onMounted, watch } from "vue";
import { BASE_URL } from "../../services/api/http";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
  errors: {
    type: Object,
    default: () => ({}),
  },
  data: Object,
});

const emit = defineEmits(["submit"]);

const defaultForm = {
  id: "",
  kunci_jabatan: "",
  nama_jabatan: "",
  nidn: "",
  nama_tanda_tangan: "",
  tdd: "",
  gambar: null as File | null,
  gambar_url: "",
};

const form = reactive({ ...defaultForm });

const canvasRef = ref<HTMLCanvasElement | null>(null);
let ctx: CanvasRenderingContext2D | null = null;
let drawing = false;

function initCanvas() {
  if (!canvasRef.value) return;
  ctx = canvasRef.value.getContext("2d");
  if (!ctx) return;

  ctx.lineWidth = 2;
  ctx.lineCap = "round";
  ctx.strokeStyle = "#000";
}

function startDrawing(e: MouseEvent | TouchEvent) {
  drawing = true;
  draw(e);
}

function stopDrawing() {
  drawing = false;
  if (ctx) ctx.beginPath();
  saveSignature();
}

function draw(e: MouseEvent | TouchEvent) {
  if (!drawing || !ctx || !canvasRef.value) return;

  const rect = canvasRef.value.getBoundingClientRect();
  let clientX, clientY;

  if (e instanceof MouseEvent) {
    clientX = e.clientX;
    clientY = e.clientY;
  } else {
    clientX = e.touches[0].clientX;
    clientY = e.touches[0].clientY;
  }

  const x = clientX - rect.left;
  const y = clientY - rect.top;

  ctx.lineTo(x, y);
  ctx.stroke();
  ctx.beginPath();
  ctx.moveTo(x, y);
}

function clearCanvas() {
  if (!ctx || !canvasRef.value) return;
  ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  form.tdd = "";
}

function handleFileChange(e: Event) {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.gambar = target.files[0];
    form.gambar_url = URL.createObjectURL(target.files[0]);
  }
}

function saveSignature() {
  if (!canvasRef.value) return;
  form.tdd = canvasRef.value.toDataURL("image/png");
}

function loadSignature(base64: string) {
  if (!ctx || !canvasRef.value || !base64) return;
  const img = new Image();
  img.onload = () => {
    ctx?.clearRect(0, 0, canvasRef.value!.width, canvasRef.value!.height);
    ctx?.drawImage(img, 0, 0);
  };
  img.src = base64;
}

watch(
  () => props.data,
  (val: any) => {
    if (!val) return;
    Object.assign(form, defaultForm);
    form.id = val.id || "";
    form.kunci_jabatan = val.kunci_jabatan || "";
    form.nama_jabatan = val.nama_jabatan || "";
    form.nidn = val.nidn || "";

    if (val.tanda_tangan) {
        form.nama_tanda_tangan = val.tanda_tangan.nama || "";
        form.tdd = val.tanda_tangan.tdd || "";
        const baseUrl = BASE_URL.replace("/api", "");
        form.gambar_url = val.tanda_tangan.gambar ? `${baseUrl}/${val.tanda_tangan.gambar}` : "";
    }

    if (form.tdd) {
      setTimeout(() => loadSignature(form.tdd), 200);
    }
  },
  { immediate: true }
);

onMounted(() => {
  initCanvas();
});

function submitForm() {
  emit("submit", form);
}
</script>

<template>
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-8 mx-auto">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">
              {{ isEdit ? "Edit" : "Tambah" }} Setting Jabatan & Tanda Tangan
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12 mt-4">
                <h6 class="fw-bold">Data Jabatan</h6>
                <hr />
              </div>
              <div class="col-xl-6">
                <label class="form-label">Kunci Jabatan :</label>
                <input
                  type="text"
                  v-model="form.kunci_jabatan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kunci_jabatan }"
                  placeholder="Contoh: kepala_biro_keuangan"
                  required
                />
                <div v-if="errors?.kunci_jabatan" class="invalid-feedback">
                  {{ errors.kunci_jabatan[0] }}
                </div>
                <small class="text-muted">Kunci ini akan digunakan di sistem secara unik (huruf kecil & underscore).</small>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nama Jabatan :</label>
                <input
                  type="text"
                  v-model="form.nama_jabatan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_jabatan }"
                  placeholder="Contoh: Kepala Biro Administrasi Keuangan"
                  required
                />
                <div v-if="errors?.nama_jabatan" class="invalid-feedback">
                  {{ errors.nama_jabatan[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">NIDN :</label>
                <input
                  type="text"
                  v-model="form.nidn"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nidn }"
                  placeholder="Opsional, Contoh: 0123456789"
                />
                <div v-if="errors?.nidn" class="invalid-feedback">
                  {{ errors.nidn[0] }}
                </div>
              </div>

              <div class="col-xl-12 mt-4">
                <h6 class="fw-bold">Data Penanda Tangan</h6>
                <hr />
              </div>

              <div class="col-xl-12">
                <label class="form-label">Nama Lengkap Penanda Tangan :</label>
                <input
                  type="text"
                  v-model="form.nama_tanda_tangan"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_tanda_tangan }"
                  placeholder="Contoh: Dr. H. Fulan, M.Pd."
                  required
                />
                <div v-if="errors?.nama_tanda_tangan" class="invalid-feedback">
                  {{ errors.nama_tanda_tangan[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label"
                  >Gambar Tanda Tangan (Coret di sini) :</label
                >
                <div
                  class="border rounded p-2 text-center bg-light"
                  :class="{ 'border-danger': errors?.tdd }"
                >
                  <canvas
                    ref="canvasRef"
                    width="400"
                    height="200"
                    class="bg-white border signature-canvas"
                    :class="{ 'border-danger': errors?.tdd }"
                    style="touch-action: none"
                    @mousedown="startDrawing"
                    @mousemove="draw"
                    @mouseup="stopDrawing"
                    @touchstart="startDrawing"
                    @touchmove="draw"
                    @touchend="stopDrawing"
                  ></canvas>
                  <div class="mt-2 text-start">
                    <button
                      type="button"
                      class="btn btn-sm btn-danger-light"
                      @click="clearCanvas"
                    >
                      <i class="ri-delete-bin-line"></i> Bersihkan
                    </button>
                    <small class="text-muted ms-2"
                      >Silahkan coret pada kotak di atas.</small
                    >
                  </div>
                </div>
                <div v-if="errors?.tdd" class="text-danger small mt-1">
                  {{ errors.tdd[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label"
                  >Atau Upload Gambar Tanda Tangan :</label
                >
                <input
                  type="file"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.gambar }"
                  @change="handleFileChange"
                  accept="image/*"
                />
                <div v-if="errors?.gambar" class="invalid-feedback">
                  {{ errors.gambar[0] }}
                </div>
                <div
                  v-if="form.gambar_url"
                  class="mt-2 text-center border p-2 bg-light rounded shadow-sm"
                >
                  <p class="mb-1 small text-muted">Preview Gambar:</p>
                  <img
                    :src="form.gambar_url"
                    alt="Preview"
                    class="img-fluid border rounded shadow-sm"
                    style="max-height: 150px"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer text-end">
            <button class="btn btn-primary btn-wave shadow-sm">
              {{ isEdit ? "Simpan Data" : "Tambah Data" }}
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<style scoped>
.signature-canvas {
  max-width: 100%;
  cursor: crosshair;
  background-color: white !important;
}
</style>
