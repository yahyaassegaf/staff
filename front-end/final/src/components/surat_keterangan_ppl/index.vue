<script lang="ts" setup>
import { reactive, ref, watch, onMounted, nextTick } from "vue";
import { computed } from "vue";
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
  btnLoading: {
    type: Boolean,
    default: false,
  },
});

const defaultForm = {
  id: "",
  no_surat: "",
  nomor_surat: "",
  tanda_tangan_id: 0,
  prodi_id: 0,
  nama_lengkap: null,
  nama_mhs: "",
  tanggal_lahir: "",
  tempat_lahir: "",
  nim: "",
  jenis_kelamin: "",
  prodi_mhs: "",
  alamat_rumah: "",
  kelas_pondok: "",
  tanggal: "",
};

const disableListMhsWatcher = ref(false);

const readonlyField = ref({
  nama_mhs: false,
  tanggal_lahir: false,
  tempat_lahir: false,
  jenis_kelamin: false,
  kelas_pondok: false,
  alamat_rumah: false,
  prodi_mhs: false,
});

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

  form.nama_mhs = val.nama;
  form.nim = val.nim;
  form.tanggal_lahir = val.tanggal_lahir;
  form.tempat_lahir = val.tempat_lahir;

  form.jenis_kelamin = val.jenis_kelamin || "";

  if (val.alias_prodi) {
    form.prodi_mhs = val.alias_prodi;
    readonlyField.value.prodi_mhs = true;
  } else {
    form.prodi_mhs = val.prodi_mhs;
    readonlyField.value.prodi_mhs = false;
  }

  form.alamat_rumah = val.alamat;
  form.tanggal_lahir = val.tanggal_lahir ? val.tanggal_lahir.slice(0, 10) : "";
  isLoadingData.value = false;
});

const listProdi = ref<any[]>([]);
const listTandaTangan = ref<any[]>([]);

async function getProdi() {
  try {
    const response = await apiGet(`/get-prodi`);

    if (response.success) {
      const data = response.data?.data;

      // paksa jadi array
      listProdi.value = Array.isArray(data) ? data : [data];

      if (listProdi.value.length === 1) {
        form.prodi_id = Number(listProdi.value[0].id);
      }
    }
  } catch (error) {
  }
}

async function getTandaTangan() {
  try {
    const response = await apiGet(`/tanda-tangan`);
    if (response.success) {
      const data = response.data?.data;
      listTandaTangan.value = Array.isArray(data) ? data : [data];
    }
  } catch (error) {
  }
}


const listJenisSurat = ref<any[]>([]);

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

function getRoman(num: number) {
  const roman: any = { 1: "I", 2: "II", 3: "III", 4: "IV", 5: "V", 6: "VI", 7: "VII", 8: "VIII", 9: "IX", 10: "X", 11: "XI", 12: "XII" };
  return roman[num] || "";
}

const formatParts = computed(() => {
  const getFormat = (id: number) => {
    const js = listJenisSurat.value.find((x: any) => Number(x.id) === id);
    if (!js) return "";
    let str = js.format_surat;
    
    const dateObj = form.tanggal ? new Date(form.tanggal) : new Date();
    const dd = String(dateObj.getDate()).padStart(2, "0");
    const romanBulan = getRoman(dateObj.getMonth() + 1);
    const yyyy = dateObj.getFullYear();
    
    let aliasProdi = "";
    if (typeof listProdi !== 'undefined' && listProdi.value) {
        const prodiItem = listProdi.value.find((p: any) => Number(p.id) === Number(form.prodi_id));
        aliasProdi = prodiItem ? prodiItem.alias : "";
    }
    
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

  return parseToParts(getFormat(3));
});


function extractNo(fullStr: string) {
  if (!fullStr) return "";
  const firstPart = fullStr.split("/")[0];
  return firstPart.replace("SU-", "").trim();
}

onMounted(() => {
  getJenisSurat();
  getProdi();
  getTandaTangan();
});

function customName(params: any) {
  return `${params.nama} - ${params.nim}`;
}

watch(
  () => props.modelValue,
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
    form.no_surat = extractNo(val.no_surat ?? val.nomor_surat ?? "");
    form.nomor_surat = val.nomor_surat ?? "";
    form.tanda_tangan_id = val.tanda_tangan_id ?? 0;
    form.prodi_id = val.prodi_id ?? 0;
    form.nama_lengkap = val.nama_lengkap ?? val.nama_mhs ?? null;
    form.nama_mhs = val.nama_mhs || val.nama_lengkap || "";
    form.nim = val.nim ?? "";
    form.jenis_kelamin = val.jenis_kelamin ?? "";
    form.prodi_mhs = val.prodi_mhs ?? "";
    form.alamat_rumah = val.alamat_rumah ?? "";
    form.tempat_lahir = val.tempat_lahir ?? "";
    form.kelas_pondok = val.kelas_pondok ?? "";

    form.tanggal_lahir = val.tanggal_lahir
      ? val.tanggal_lahir.slice(0, 10)
      : "";

    form.tanggal = val.tanggal ? val.tanggal.slice(0, 10) : "";

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
              {{ isEdit ? "Edit" : "Tambah" }} Surat Keterangan PPL
            </div>
          </div>
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-xl-12">
                <label class="form-label">Nomor Surat:</label>
                
                <div class="input-group">
                  <span class="input-group-text" v-if="formatParts.prefix">{{ formatParts.prefix }}</span>
                  <input
                    type="text"
                    v-model="form.no_surat"
                    class="form-control"
                    :class="{ 'is-invalid': errors?.nomor_surat || errors?.no_surat }"
                    placeholder="No"
                  />
                  <span class="input-group-text" v-if="formatParts.suffix">{{ formatParts.suffix }}</span>
                  <div v-if="errors?.nomor_surat || errors?.no_surat" class="invalid-feedback">
                    {{ errors?.nomor_surat ? errors.nomor_surat[0] : errors?.no_surat[0] }}
                  </div>
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-prodi" class="form-label"
                  >Program Studi:</label
                >
                <select
                  class="form-select"
                  :class="{ 'is-invalid': errors?.prodi_id }"
                  v-model="form.prodi_id"
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
                <label for="input-nim" class="form-label"
                  >Nim Mahasiswa :</label
                >
                <input type="hidden" v-if="isEdit" v-model="form.id" />
                <Multiselect
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
                <div v-if="errors?.nim" class="text-danger small">
                  {{ errors.nim[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-nama-mhs" class="form-label"
                  >Nama Mahasiswa :</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.nama_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.nama_mhs }"
                  :readonly="readonlyField.nama_mhs"
                  id="input-nama-mhs"
                  placeholder="Isikan Nama Mahasiswa"
                />
                <div v-if="errors?.nama_mhs" class="invalid-feedback">
                  {{ errors.nama_mhs[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-tempat-lahir" class="form-label"
                  >Tempat Lahir:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.tempat_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tempat_lahir }"
                  id="input-tempat-lahir"
                  placeholder="Isikan Tempat Lahir"
                />
                <div v-if="errors?.tempat_lahir" class="invalid-feedback">
                  {{ errors.tempat_lahir[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-tanggal-lahir" class="form-label"
                  >Tanggal Lahir:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal_lahir"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal_lahir }"
                  id="input-tanggal-lahir"
                />
                <div v-if="errors?.tanggal_lahir" class="invalid-feedback">
                  {{ errors.tanggal_lahir[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-prodi-mhs" class="form-label"
                  >Prodi Mahasiswa:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.prodi_mhs"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.prodi_mhs }"
                  id="input-prodi-mhs"
                  :readonly="readonlyField.prodi_mhs"
                  placeholder="Isikan Prodi Mahasiswa"
                />
                <div v-if="errors?.prodi_mhs" class="invalid-feedback">
                  {{ errors.prodi_mhs[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-kelas-pondok" class="form-label"
                  >Kelas Pondok:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="text"
                  v-model="form.kelas_pondok"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.kelas_pondok }"
                  id="input-kelas-pondok"
                  placeholder="Isikan Kelas Pondok"
                />
                <div v-if="errors?.kelas_pondok" class="invalid-feedback">
                  {{ errors.kelas_pondok[0] }}
                </div>
              </div>
              <div class="col-xl-6">
                <label for="input-tanda-tangan" class="form-label"
                  >Ketua PPL:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <select
                  v-else
                  class="form-select"
                  :class="{ 'is-invalid': errors?.tanda_tangan_id }"
                  v-model="form.tanda_tangan_id"
                  id="input-tanda-tangan"
                >
                  <option value="0" disabled>Pilih Ketua PPL</option>
                  <option
                    v-for="ttd in listTandaTangan"
                    :key="ttd.id"
                    :value="Number(ttd.id)"
                  >
                    {{ ttd.nama }}
                  </option>
                </select>
                <div v-if="errors?.tanda_tangan_id" class="invalid-feedback">
                  {{ errors.tanda_tangan_id[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-tanggal" class="form-label"
                  >Tanggal Surat:</label
                >
                <div v-if="isLoadingData" class="skeleton-input"></div>
                <input
                  v-else
                  type="date"
                  v-model="form.tanggal"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.tanggal }"
                  id="input-tanggal"
                />
                <div v-if="errors?.tanggal" class="invalid-feedback">
                  {{ errors.tanggal[0] }}
                </div>
              </div>
              <div class="col-xl-12">
                <label for="input-alamat" class="form-label">Alamat:</label>
                <div
                  v-if="isLoadingData"
                  class="skeleton-input"
                  style="height: 62px"
                ></div>
                <textarea
                  v-else
                  v-model="form.alamat_rumah"
                  class="form-control"
                  :class="{ 'is-invalid': errors?.alamat_rumah }"
                  id="input-alamat"
                  placeholder="Isikan Alamat"
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
