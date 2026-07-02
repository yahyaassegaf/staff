<script lang="ts">
import { defineComponent, onMounted, ref, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { apiGet, apiPost } from "../../../services/api/request";
import SimpleCardComponent from "../../../shared/components/@spk/simple-card.vue";
import { toast } from "vue3-toastify";

export default defineComponent({
  components: { SimpleCardComponent },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const id = route.params.id;

    const loading = ref(false);
    const saving = ref(false);
    const loadingNilai = ref(false);

    const mahasiswa = ref<any>(null);
    const transkip = ref<any>({
      ipk: null,
      judul_skripsi: "",
      predikat_kelulusan: ""
    });

    const nilaiSiakad = ref<any[]>([]);
    
    // Checkbox mapping array of booleans matching nilaiSiakad
    const selectedNilai = ref<boolean[]>([]);
    const selectAll = ref(false);

    async function getDetail() {
      try {
        loading.value = true;
        const res = await apiGet(`/transkip/${id}`);
        if (res.success && res.data?.status) {
          mahasiswa.value = res.data.data;
          
          if (res.data.data.transkip) {
            transkip.value.ipk = res.data.data.transkip.ipk;
            transkip.value.judul_skripsi = res.data.data.transkip.judul_skripsi;
            transkip.value.predikat_kelulusan = res.data.data.transkip.predikat_kelulusan;
          }
        }
      } catch (err) {
        toast.error("Gagal load data detail", { autoClose: 2000 });
      } finally {
        loading.value = false;
      }
    }

    async function loadNilaiSiakad() {
      try {
        loadingNilai.value = true;
        const res = await apiGet(`/transkip/${id}/nilai`);
        if (res.success && res.data?.status) {
          nilaiSiakad.value = res.data.data;
          
          // Match with saved nilai if any
          const savedNilai = mahasiswa.value?.nilai_mahasiswa || [];
          
          selectedNilai.value = nilaiSiakad.value.map((n: any) => {
            const found = savedNilai.find((sn: any) => sn.kode_mk === n.kode_mk);
            return found ? found.transkrip === 'Y' : true; // Default selected
          });
          
          updateSelectAllState();
        }
      } catch (err) {
        toast.error("Gagal memuat nilai dari SIAKAD", { autoClose: 2000 });
      } finally {
        loadingNilai.value = false;
      }
    }

    function toggleSelectAll() {
      selectedNilai.value = selectedNilai.value.map(() => selectAll.value);
    }

    function updateSelectAllState() {
      selectAll.value = selectedNilai.value.every(v => v);
    }

    async function saveTranskipData() {
      try {
        saving.value = true;
        // 1. Save Transkip (IPK, Judul)
        const payloadTranskip = {
          mahasiswa_id: id,
          ipk: transkip.value.ipk,
          judul_skripsi: transkip.value.judul_skripsi
        };
        const resTranskip = await apiPost('/transkip', payloadTranskip);
        
        if (resTranskip.success && resTranskip.data?.status) {
          transkip.value.predikat_kelulusan = resTranskip.data.data.predikat_kelulusan;
        }

        // 2. Save Nilai
        const tgl = new Date().toISOString().split('T')[0];
        const payloadNilai = {
          mahasiswa_id: id,
          tanggal_nilai: tgl,
          nilai: nilaiSiakad.value.map((n, i) => ({
            kode_mk: n.kode_mk,
            nama_mk: n.nama_mk,
            sks_mk: n.sks_mk,
            smt_mk: n.smt_mk,
            nilai_akhir: n.nilai_akhir,
            nilai_bobot: n.nilai_bobot,
            nilai_huruf: n.nilai_huruf,
            transkrip: selectedNilai.value[i] ? 'Y' : 'T'
          }))
        };

        const resNilai = await apiPost('/transkip/nilai', payloadNilai);
        
        if (resTranskip.success && resNilai.success) {
          toast.success("Data berhasil disimpan", { autoClose: 2000 });
          // Redirect to transkrip menu after a short delay to allow toast to show
          setTimeout(() => {
            router.push('/transkip');
          }, 1000);
        } else {
          toast.error("Terjadi kesalahan saat menyimpan", { autoClose: 2000 });
        }

      } catch (err) {
        toast.error("Gagal menyimpan", { autoClose: 2000 });
      } finally {
        saving.value = false;
      }
    }
    const printedNilai = computed(() => {
      return nilaiSiakad.value.filter((_, i) => selectedNilai.value[i]);
    });

    const leftColumnNilai = computed(() => {
      const half = Math.ceil(printedNilai.value.length / 2);
      return printedNilai.value.slice(0, half);
    });

    const rightColumnNilai = computed(() => {
      const half = Math.ceil(printedNilai.value.length / 2);
      return printedNilai.value.slice(half);
    });

    const calculateTotals = (items: any[]) => {
      return items.reduce(
        (acc, item) => {
          acc.sks += Number(item.sks_mk) || 0;
          // Asumsi mutu (M) = SKS * Bobot sesuai contoh tabel (misal 2 * 3.25 = 6.50)
          acc.mutu += (Number(item.sks_mk) || 0) * (Number(item.nilai_bobot) || 0);
          return acc;
        },
        { sks: 0, mutu: 0 }
      );
    };

    const leftTotals = computed(() => calculateTotals(leftColumnNilai.value));
    const rightTotals = computed(() => calculateTotals(rightColumnNilai.value));

    function printTranskrip() {
      window.print();
    }

    onMounted(async () => {
      await getDetail();
      loadNilaiSiakad(); // Lazy load nilai SIAKAD
    });

    return {
      mahasiswa,
      transkip,
      nilaiSiakad,
      selectedNilai,
      selectAll,
      loading,
      loadingNilai,
      saving,
      toggleSelectAll,
      updateSelectAllState,
      saveTranskipData,
      printTranskrip,
      printedNilai,
      leftColumnNilai,
      rightColumnNilai,
      leftTotals,
      rightTotals
    };
  }
});
</script>

<template>
  <div class="container-fluid">
    <div class="d-print-none">
      <div class="d-md-flex align-items-center justify-content-between my-4 page-header-breadcrumb">
      <h1 class="page-title fw-semibold fs-18 mb-0">Detail Transkrip</h1>
      <div class="ms-md-1 ms-0">
        <nav>
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Akademik</a></li>
            <li class="breadcrumb-item"><router-link to="/transkip">Transkrip Nilai</router-link></li>
            <li class="breadcrumb-item active" aria-current="page">Detail</li>
          </ol>
        </nav>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <template v-else-if="mahasiswa">
      <div class="row">
        <!-- Info Mahasiswa & Form IPK -->
        <div class="col-xl-12">
          <SimpleCardComponent title="Informasi Transkrip">
            <div class="row gy-3">
              <div class="col-md-6">
                <table class="table table-borderless table-sm">
                  <tbody>
                    <tr>
                      <th width="150">NIM</th>
                      <td>: {{ mahasiswa.nim }}</td>
                    </tr>
                    <tr>
                      <th>Nama</th>
                      <td>: {{ mahasiswa.nama }}</td>
                    </tr>
                    <tr>
                      <th>Program Studi</th>
                      <td>: {{ mahasiswa.prodi?.nama }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Judul Skripsi</label>
                  <textarea class="form-control" v-model="transkip.judul_skripsi" rows="2"></textarea>
                </div>
                <div class="row">
                  <div class="col-md-12 mb-3">
                    <label class="form-label">IPK</label>
                    <input type="number" step="0.01" class="form-control" v-model="transkip.ipk" placeholder="Contoh: 3.75" />
                  </div>
                </div>
              </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-3">
              <button class="btn btn-primary" @click="saveTranskipData" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="ri-save-line me-1"></i> Simpan
              </button>
              <button class="btn btn-success" @click="printTranskrip" :disabled="saving">
                <i class="ri-printer-line me-1"></i> Print Transkrip
              </button>
            </div>
          </SimpleCardComponent>
        </div>

        <!-- Tabel Nilai -->
        <div class="col-xl-12 mt-4">
          <SimpleCardComponent title="Daftar Nilai (SIAKAD)">
            <div v-if="loadingNilai" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else-if="nilaiSiakad.length === 0" class="text-center py-4 text-muted">
              Tidak ada data nilai ditemukan untuk mahasiswa ini.
            </div>
            <div class="table-responsive" v-else>
              <table class="table table-bordered table-striped table-hover mt-3">
                <thead class="table-primary">
                  <tr>
                    <th class="text-center" width="50">
                      <input class="form-check-input" type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                    </th>
                    <th class="text-center">No</th>
                    <th>Kode MK</th>
                    <th>Nama MK</th>
                    <th class="text-center">SKS</th>
                    <th class="text-center">Smt</th>
                    <th class="text-center">N. Akhir</th>
                    <th class="text-center">N. Bobot</th>
                    <th class="text-center">Huruf</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in nilaiSiakad" :key="index">
                    <td class="text-center">
                      <input class="form-check-input" type="checkbox" v-model="selectedNilai[index]" @change="updateSelectAllState">
                    </td>
                    <td class="text-center">{{ index + 1 }}</td>
                    <td>{{ item.kode_mk }}</td>
                    <td>{{ item.nama_mk }}</td>
                    <td class="text-center">{{ item.sks_mk }}</td>
                    <td class="text-center">{{ item.smt_mk }}</td>
                    <td class="text-center">{{ item.nilai_akhir }}</td>
                    <td class="text-center">{{ item.nilai_bobot }}</td>
                    <td class="text-center">{{ item.nilai_huruf }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </SimpleCardComponent>
        </div>
      </div>
    </template>
    </div> <!-- End of d-print-none -->

    <!-- Print Area -->
    <div id="print-area" class="d-none d-print-block border border-dark bg-white" v-if="mahasiswa">
      <div class="print-header text-center fw-bold border-bottom border-dark p-2">
        <div>UNIVERSITAS ISLAM INTERNASIONAL DARULLUGHAH WADDA'WAH</div>
        <div>TRANSKRIP AKADEMIK</div>
        <div class="fw-normal">Nomor : _________________</div>
      </div>

      <div class="p-2 border-bottom border-dark">
        <table class="table-info-mahasiswa table-borderless">
          <tr>
            <td width="20%">Nama Mahasiswa</td>
            <td width="2%">:</td>
            <td width="28%">{{ mahasiswa?.nama }}</td>
            <td width="20%">Program Pendidikan</td>
            <td width="2%">:</td>
            <td width="28%">S1</td>
          </tr>
          <tr>
            <td>Nomor Pokok Mahasiswa</td>
            <td>:</td>
            <td>{{ mahasiswa?.nim }}</td>
            <td>Fakultas</td>
            <td>:</td>
            <td>{{ mahasiswa?.prodi?.fakultas_prodi?.fakultas?.nama || 'Tarbiyah' }}</td>
          </tr>
          <tr>
            <td>Nomor Ijazah Nasional</td>
            <td>:</td>
            <td>{{ mahasiswa?.nomor_ijazah_nasional }}</td>
            <td>Program Studi</td>
            <td>:</td>
            <td>{{ mahasiswa?.prodi?.nama }}</td>
          </tr>
          <tr>
            <td>Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>{{ mahasiswa?.tempat_lahir }}, {{ mahasiswa?.tgl_lahir }}</td>
            <td>Nomor SK BAN-PT</td>
            <td>:</td>
            <td>{{ mahasiswa?.nomor_sk_ban_pt || '10034/SK/BAN-PT/Ak/S/III/2022' }}</td>
          </tr>
          <tr>
            <td>Tanggal Kelulusan</td>
            <td>:</td>
            <td>{{ mahasiswa?.tanggal_sk_yudisium }}</td>
            <td></td><td></td><td></td>
          </tr>
        </table>
      </div>

      <div class="d-flex p-2 border-bottom border-dark print-tables-container">
        <!-- Left Table -->
        <table class="print-table w-50 me-1">
          <thead>
            <tr>
              <th rowspan="2" width="5%">NO</th>
              <th rowspan="2" width="60%">MATA KULIAH</th>
              <th rowspan="2" width="10%">SKS<br>S</th>
              <th colspan="2" width="25%">NILAI</th>
            </tr>
            <tr>
              <th>HM</th>
              <th>M</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in leftColumnNilai" :key="'l'+index">
              <td class="text-center">{{ index + 1 }}</td>
              <td>{{ item.nama_mk }}</td>
              <td class="text-center">{{ item.sks_mk }}</td>
              <td class="text-center">{{ item.nilai_huruf }}</td>
              <td class="text-center">{{ (Number(item.sks_mk) * Number(item.nilai_bobot)).toFixed(2) }}</td>
            </tr>
            <tr>
              <td colspan="2" class="text-end fw-bold"></td>
              <td class="text-center fw-bold">{{ leftTotals.sks }}</td>
              <td></td>
              <td class="text-center fw-bold">{{ leftTotals.mutu.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>

        <!-- Right Table -->
        <table class="print-table w-50 ms-1">
          <thead>
            <tr>
              <th rowspan="2" width="5%">NO</th>
              <th rowspan="2" width="60%">MATA KULIAH</th>
              <th rowspan="2" width="10%">SKS<br>S</th>
              <th colspan="2" width="25%">NILAI</th>
            </tr>
            <tr>
              <th>HM</th>
              <th>M</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in rightColumnNilai" :key="'r'+index">
              <td class="text-center">{{ leftColumnNilai.length + index + 1 }}</td>
              <td>{{ item.nama_mk }}</td>
              <td class="text-center">{{ item.sks_mk }}</td>
              <td class="text-center">{{ item.nilai_huruf }}</td>
              <td class="text-center">{{ (Number(item.sks_mk) * Number(item.nilai_bobot)).toFixed(2) }}</td>
            </tr>
            <tr v-for="i in (leftColumnNilai.length - rightColumnNilai.length)" :key="'filler'+i">
              <td>&nbsp;</td><td></td><td></td><td></td><td></td>
            </tr>
            <tr>
              <td colspan="2" class="text-end fw-bold"></td>
              <td class="text-center fw-bold">{{ rightTotals.sks }}</td>
              <td></td>
              <td class="text-center fw-bold">{{ rightTotals.mutu.toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer Info -->
      <div class="print-footer p-2">
        <table class="w-100 table-borderless">
          <tr>
            <td width="20%">Indeks Prestasi Kumulatif</td>
            <td width="2%">:</td>
            <td width="48%">{{ transkip.ipk }}</td>
            <td width="30%" class="text-center">Bangil, {{ new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) }}</td>
          </tr>
          <tr>
            <td>Predikat Kelulusan</td>
            <td>:</td>
            <td>{{ transkip.predikat_kelulusan }}</td>
            <td class="text-center">Dekan,</td>
          </tr>
          <tr>
            <td class="align-top">Judul Skripsi</td>
            <td class="align-top">:</td>
            <td class="align-top">{{ transkip.judul_skripsi }}</td>
            <td class="text-center" style="height: 65px; vertical-align: bottom;">
              <div class="fw-bold text-decoration-underline">{{ mahasiswa?.prodi?.fakultas_prodi?.fakultas?.dekan || 'Dr. Junaidi, M.Pd.I.' }}</div>
              <div>NIDN. {{ mahasiswa?.prodi?.fakultas_prodi?.fakultas?.nidn || '2118086802' }}</div>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
@media print {
  @page {
    size: A4 portrait;
    margin: 0; /* Atur ke 0 agar browser tidak memaksakan margin bawaan yang besar */
  }
  #print-area {
    width: 100%;
    padding: 0.5cm 1cm; /* Gunakan padding untuk mengatur jarak secara manual (0.5cm atas-bawah, 1cm kiri-kanan) */
    box-sizing: border-box;
    font-family: "Times New Roman", Times, serif;
    font-size: 10pt;
    color: #000;
  }
  .print-header {
    font-size: 14pt;
  }
  .table-info-mahasiswa {
    width: 100%;
    font-size: 10pt;
    font-weight: bold;
    margin: 0;
  }
  .table-info-mahasiswa td {
    padding: 2px 0;
  }
  .print-table {
    border-collapse: collapse;
    width: 100%;
    font-size: 9pt;
  }
  .print-table th, .print-table td {
    border: 1px solid #000;
    padding: 2px 4px; /* Kurangi padding tabel sedikit agar lebih hemat tempat */
  }
  .print-table th {
    text-align: center;
    font-weight: bold;
    background-color: #f8f9fa !important;
    -webkit-print-color-adjust: exact;
  }
  .print-footer {
    font-size: 10pt;
    font-weight: bold;
  }
  .print-footer table td {
    padding: 2px;
  }
  .print-footer table {
    margin: 0;
  }
}
</style>

<style>
@media print {
  body, html {
    background-color: white !important;
  }
  .main-content, .app-content, .page, .page-container, .container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
  }
}
</style>
