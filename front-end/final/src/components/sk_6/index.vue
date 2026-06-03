<script lang="ts" setup>
import { reactive, ref, watch, onMounted, nextTick } from "vue";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.min.css";
import { apiGet } from "../../services/api/request";
import { debounce } from "vuetify/lib/util/helpers.mjs";

const props = defineProps({
  modelValue: Object,
  isEdit: Boolean,
  errors: {
    type: Object,
    default: () => ({}),
  },
  data: Object,
  btnLoading: {
    type: Boolean,
    default: false,
  },
});

const defaultForm = {
  id: "",
  prodi_id: 0,
  nama_mhs: "",
  tanggal_lahir: "",
  tempat_lahir: "",
  nim: "",
  prodi_mhs: "",
  alamat_rumah: "",
  kelas_pondok: "",
  tanggal: "",
  nomor_surat_sklmk: "",
  nomor_surat_skak: "",
  nomor_surat_sktkp: "",
  nomor_surat_skqa: "",
  nomor_surat_skukd: "",
  no_sklmk: "",
  no_skak: "",
  no_sktkp: "",
  no_skqa: "",
  no_skukd: "",
  tanggal_berlaku_dari: "",
  tanggal_berlaku_sampai: "",
};

const disableListMhsWatcher = ref(false);



const form = reactive({ ...defaultForm });

const options = ref<any[]>([]);
const loading = ref(false);
const isLoadingData = ref(false);
const listMhs = ref<any>(null);

watch(listMhs, async (val) => {
  if (!val) return;

  if (disableListMhsWatcher.value) return;

  isLoadingData.value = true;
  await new Promise((resolve) => setTimeout(resolve, 500));

  if (val.nama) form.nama_mhs = val.nama;
  if (val.nim) form.nim = val.nim;
  
  if (val.tanggal_lahir) {
    form.tanggal_lahir = val.tanggal_lahir.slice(0, 10);
  }
  
  if (val.tempat_lahir !== undefined) form.tempat_lahir = val.tempat_lahir;

  if (val.alias_prodi) {
    form.prodi_mhs = val.alias_prodi;
  } else if (val.prodi_mhs !== undefined) {
    form.prodi_mhs = val.prodi_mhs;
  }

  if (val.alamat !== undefined) {
    form.alamat_rumah = val.alamat;
  }
  
  isLoadingData.value = false;
});

const listProdi = ref<any[]>([]);
const listJenisSurat = ref<any[]>([]);

async function getProdi() {
  try {
    const response = await apiGet(`/get-prodi`);
    if (response.success) {
      const data = response.data?.data;
      listProdi.value = Array.isArray(data) ? data : [data];

      if (listProdi.value.length === 1 && !props.isEdit) {
        form.prodi_id = Number(listProdi.value[0].id);
      }
    }
  } catch (error) {
  }
}

async function getJenisSurat() {
  try {
    const response = await apiGet(`/jenis-surat`);
    if (response.success) {
      const data = response.data?.data || response.data;
      listJenisSurat.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
  }
}

import { computed } from "vue";

function getRoman(num: number) {
  const roman: any = { 1: "I", 2: "II", 3: "III", 4: "IV", 5: "V", 6: "VI", 7: "VII", 8: "VIII", 9: "IX", 10: "X", 11: "XI", 12: "XII" };
  return roman[num] || "";
}

const formatParts = computed(() => {
  const parts = {
    sklmk: { prefix: "SU-", suffix: "" },
    skak: { prefix: "SU-", suffix: "" },
    sktkp: { prefix: "SU-", suffix: "" },
    skukd: { prefix: "SU-", suffix: "" },
    skqa: { prefix: "", suffix: "" },
  };

  const getFormat = (id: number) => {
    const js = listJenisSurat.value.find((x: any) => Number(x.id) === id);
    if (!js) return "";
    let str = js.format_surat;
    
    const dateObj = form.tanggal ? new Date(form.tanggal) : new Date();
    const dd = String(dateObj.getDate()).padStart(2, "0");
    const romanBulan = getRoman(dateObj.getMonth() + 1);
    const yyyy = dateObj.getFullYear();
    
    const prodiItem = listProdi.value.find((p) => Number(p.id) === Number(form.prodi_id));
    const aliasProdi = prodiItem ? prodiItem.alias : "";
    
    str = str.replace(/{TGL}/g, dd)
             .replace(/{BULAN}/g, romanBulan)
             .replace(/{TAHUN}/g, String(yyyy))
             .replace(/{PRODI}/g, aliasProdi);
             
    return str;
  };

  const parseToParts = (str: string) => {
    if(!str) return { prefix: "SU-", suffix: "" };
    const splitted = str.split("{NO}");
    return {
      prefix: splitted[0] || "",
      suffix: splitted[1] || ""
    };
  };

  parts.sklmk = parseToParts(getFormat(2));
  parts.skak = parseToParts(getFormat(1));
  parts.sktkp = parseToParts(getFormat(3));
  parts.skukd = parseToParts(getFormat(4));
  parts.skqa = parseToParts(getFormat(5));

  return parts;
});

function extractNo(fullStr: string) {
  if (!fullStr) return "";
  const firstPart = fullStr.split("/")[0];
  return firstPart.replace("SU-", "").trim();
}

onMounted(() => {
  getProdi();
  getJenisSurat();
});

function customName(params: any) {
  return `${params.nama} - ${params.nim}`;
}

watch(
  () => props.data,
  async (val) => {
    if (!props.isEdit) return;

    if (!val || !val.nim) {
      isLoadingData.value = true;
      return;
    }

    disableListMhsWatcher.value = true;
    isLoadingData.value = true;

    await new Promise((resolve) => setTimeout(resolve, 500));

    Object.assign(form, val);

    form.id = val.id ?? "";
    form.prodi_id = val.prodi_id ?? 0;
    form.nama_mhs = val.nama_mhs || "";
    form.nim = val.nim ?? "";
    form.prodi_mhs = val.prodi_mhs ?? "";
    form.alamat_rumah = val.alamat_rumah ?? "";
    form.tempat_lahir = val.tempat_lahir ?? "";
    form.kelas_pondok = val.kelas_pondok ?? "";
    form.tanggal_lahir = val.tanggal_lahir ? val.tanggal_lahir.slice(0, 10) : "";
    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";
    form.nomor_surat_sklmk = val.nomor_surat_sklmk ?? "";
    form.nomor_surat_skak = val.nomor_surat_skak ?? "";
    form.nomor_surat_sktkp = val.nomor_surat_sktkp ?? "";
    form.nomor_surat_skqa = val.nomor_surat_skqa ?? "";
    form.nomor_surat_skukd = val.nomor_surat_skukd ?? "";
    
    form.no_sklmk = extractNo(form.nomor_surat_sklmk);
    form.no_skak = extractNo(form.nomor_surat_skak);
    form.no_sktkp = extractNo(form.nomor_surat_sktkp);
    form.no_skqa = extractNo(form.nomor_surat_skqa);
    form.no_skukd = extractNo(form.nomor_surat_skukd);

    form.tanggal_berlaku_dari = val.tanggal_berlaku_dari ? val.tanggal_berlaku_dari.slice(0, 10) : "";
    form.tanggal_berlaku_sampai = val.tanggal_berlaku_sampai ? val.tanggal_berlaku_sampai.slice(0, 10) : "";

    if (form.nim) {
      listMhs.value = {
        nim: form.nim,
        nama: form.nama_mhs,
        id: form.nim,
      };
      options.value = [listMhs.value];
    }

    isLoadingData.value = false;

    await nextTick();
    disableListMhsWatcher.value = false;
  },
  { immediate: true }
);

const getMhs = debounce(async (params: string) => {
  const keyword = params.trim();
  if (!keyword && !props.isEdit) {
    options.value = [];
    return;
  }

  try {
    loading.value = true;
    const response = await apiGet(`/get-mhs`, { search: keyword });
    if (response.success) {
      const result = response.data;
      if (result && result.data && Array.isArray(result.data)) {
        options.value = result.data;
      } else if (Array.isArray(result)) {
        options.value = result;
      } else {
        options.value = [];
      }
    }
  } catch (error) {
  } finally {
    loading.value = false;
  }
}, 300);

const emit = defineEmits(["submit"]);

function submitForm() {
  emit("submit", form);
}
</script>

<template>
  <div class="row">
    <form @submit.prevent="submitForm">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-header">
            <div class="card-title">
              {{ isEdit ? "Edit" : "Tambah" }} SK 6 (5 Surat Keterangan)
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label for="input-prodi" class="form-label">Program Studi Unit:</label>
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.prodi_id }"
                  v-model="form.prodi_id"
                  id="input-prodi"
                >
                  <option
                    v-for="prodi in listProdi"
                    :key="prodi.id"
                    :value="Number(prodi.id)"
                  >
                    {{ prodi.nama }}
                  </option>
                </select>
                <div v-if="errors?.prodi_id" class="invalid-feedback">
                  {{ errors.prodi_id[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-mhs" class="form-label">Mahasiswa:</label>
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <Multiselect
                  id="input-mhs"
                  :options="options"
                  v-model="listMhs"
                  :internal-search="false"
                  @search-change="getMhs"
                  label="nama"
                  track-by="id"
                  :searchable="true"
                  :loading="loading"
                  :custom-label="customName"
                  :class="{ 'border-danger': errors?.nim || errors?.nama_mhs }"
                ></Multiselect>
                <div v-if="errors?.nim" class="text-danger small mt-1">
                  {{ errors.nim[0] }}
                </div>
                <div v-else-if="errors?.nama_mhs" class="text-danger small mt-1">
                  {{ errors.nama_mhs[0] }}
                </div>
              </div>

              <!-- Nomor Surat Section -->
              <div class="col-xl-12 mt-4">
                <h6 class="fw-bold">Nomor Surat Keterangan</h6>
                <hr />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nomor SK Lulus Mata Kuliah:</label>
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.sklmk.prefix">{{ formatParts.sklmk.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_sklmk"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat_sklmk || errors?.no_sklmk }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.sklmk.suffix">{{ formatParts.sklmk.suffix }}</span>
                  <div v-if="errors?.nomor_surat_sklmk || errors?.no_sklmk" class="invalid-feedback">
                    {{ errors.nomor_surat_sklmk ? errors.nomor_surat_sklmk[0] : errors.no_sklmk[0] }}
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nomor SK Administrasi Keuangan:</label>
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.skak.prefix">{{ formatParts.skak.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_skak"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat_skak || errors?.no_skak }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.skak.suffix">{{ formatParts.skak.suffix }}</span>
                  <div v-if="errors?.nomor_surat_skak || errors?.no_skak" class="invalid-feedback">
                    {{ errors.nomor_surat_skak ? errors.nomor_surat_skak[0] : errors.no_skak[0] }}
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nomor SK Tasma, KKN, PPL:</label>
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.sktkp.prefix">{{ formatParts.sktkp.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_sktkp"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat_sktkp || errors?.no_sktkp }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.sktkp.suffix">{{ formatParts.sktkp.suffix }}</span>
                  <div v-if="errors?.nomor_surat_sktkp || errors?.no_sktkp" class="invalid-feedback">
                    {{ errors.nomor_surat_sktkp ? errors.nomor_surat_sktkp[0] : errors.no_sktkp[0] }}
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nomor SK Ujian Komprehensif Diniyah:</label>
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.skukd.prefix">{{ formatParts.skukd.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_skukd"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat_skukd || errors?.no_skukd }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.skukd.suffix">{{ formatParts.skukd.suffix }}</span>
                  <div v-if="errors?.nomor_surat_skukd || errors?.no_skukd" class="invalid-feedback">
                    {{ errors.nomor_surat_skukd ? errors.nomor_surat_skukd[0] : errors.no_skukd[0] }}
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nomor SK Qismul Aman:</label>
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.skqa.prefix">{{ formatParts.skqa.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_skqa"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat_skqa || errors?.no_skqa }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.skqa.suffix">{{ formatParts.skqa.suffix }}</span>
                  <div v-if="errors?.nomor_surat_skqa || errors?.no_skqa" class="invalid-feedback">
                    {{ errors.nomor_surat_skqa ? errors.nomor_surat_skqa[0] : errors.no_skqa[0] }}
                  </div>
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tanggal Surat:</label>
                <input
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal }"
                />
                <div v-if="errors?.tanggal" class="invalid-feedback">
                  {{ errors.tanggal[0] }}
                </div>
              </div>

              <!-- Tanggal Berlaku (Qismul Aman) -->
              <div class="col-xl-6">
                <label class="form-label">Tanggal Mulai Berlaku (Khusus SK QA):</label>
                <input
                  type="date"
                  v-model="form.tanggal_berlaku_dari"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_berlaku_dari }"
                />
                <div v-if="errors?.tanggal_berlaku_dari" class="invalid-feedback">
                  {{ errors.tanggal_berlaku_dari[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label class="form-label">Tanggal Akhir Berlaku (Khusus SK QA):</label>
                <input
                  type="date"
                  v-model="form.tanggal_berlaku_sampai"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_berlaku_sampai }"
                />
                <div v-if="errors?.tanggal_berlaku_sampai" class="invalid-feedback">
                  {{ errors.tanggal_berlaku_sampai[0] }}
                </div>
              </div>

              <!-- Data Detail Section -->
              <div class="col-xl-12 mt-4">
                <h6 class="fw-bold">Data Detail Mahasiswa</h6>
                <hr />
              </div>

              <div class="col-xl-6">
                <label class="form-label">Nama Mahasiswa:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_mhs }"
                  readonly
                />
                <div v-if="errors?.nama_mhs" class="invalid-feedback">
                  {{ errors.nama_mhs[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tempat Lahir:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tempat_lahir }"
                />
                <div v-if="errors?.tempat_lahir" class="invalid-feedback">
                  {{ errors.tempat_lahir[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Tanggal Lahir:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_lahir }"
                />
                <div v-if="errors?.tanggal_lahir" class="invalid-feedback">
                  {{ errors.tanggal_lahir[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Prodi Mahasiswa:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.prodi_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.prodi_mhs }"
                  readonly
                />
                <div v-if="errors?.prodi_mhs" class="invalid-feedback">
                  {{ errors.prodi_mhs[0] }}
                </div>
              </div>

              <div class="col-xl-6">
                <label class="form-label">Kelas Pondok:</label>
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.kelas_pondok"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kelas_pondok }"
                />
                <div v-if="errors?.kelas_pondok" class="invalid-feedback">
                  {{ errors.kelas_pondok[0] }}
                </div>
              </div>

              <div class="col-xl-12">
                <label class="form-label">Alamat Rumah:</label>
                <div v-if="isLoadingData" class="skeleton-input" style="height: 62px"></div>
                <textarea
                  v-else
                  v-model="form.alamat_rumah"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_rumah }"
                ></textarea>
                <div v-if="errors?.alamat_rumah" class="invalid-feedback">
                  {{ errors.alamat_rumah[0] }}
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-primary-light btn-wave ms-auto float-end" :disabled="btnLoading">
              <span v-if="btnLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              {{ btnLoading ? (isEdit ? "Mengupdate..." : "Menyimpan...") : (isEdit ? "Update" : "Simpan") }}
            </button>
          </div>
        </div>
      </div>
    </form>
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
</style>
