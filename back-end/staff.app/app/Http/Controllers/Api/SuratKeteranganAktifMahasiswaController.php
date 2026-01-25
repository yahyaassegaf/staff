<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganAktifMahasiswa;
use App\Models\ThAkademik;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganAktifMahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id');

        $data->select(
            'surat_keterangan_aktif_mahasiswa.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan_aktif_mahasiswa.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan_aktif_mahasiswa.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('tahun_akademik', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_aktif_mahasiswa.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_aktif_mahasiswa.jenis_kelamin', 'P');
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'desc')
        );

        $data = $data->paginate($request->input('limit', 10));

        return response()->json(
            [
                'status' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil'
            ]
        );
    }

    public function store(Request $request)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required|exists:prodi,id',
                'th_akademik_id' => 'nullable|exists:th_akademik,id',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'nik' => 'nullable|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'prodi_mhs' => 'required|string|max:255',
                'semester' => 'required|string|max:50',
                'tahun_akademik' => 'nullable|string|max:100',
                'nama_ortu' => 'required|string|max:255',
                'nik_ortu' => 'nullable|string|max:255',
                'nip_ortu' => 'nullable|string|max:255',
                'alamat_ortu' => 'required|string',
                'hp_ortu' => 'nullable|string|max:50',
                'tanggal' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            // $login = Auth::user()->prodi ? Auth::user()->prodi->alias : 'UMUM';
            $login = $request->prodi_mhs;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            // $unit = 'K.' . strtoupper($login);
            $unit = 'K.' . strtoupper($login);

            $noSurat = SuratService::NoSuratKeteranganAktifMahasiswa($no_surat, $unit);

            $skam = new SuratKeteranganAktifMahasiswa();
            $skam->nomor_surat = $noSurat;
            $skam->prodi_id = $validate['prodi_id'];
            $skam->th_akademik_id = $validate['th_akademik_id'] ?? null;
            $skam->nama_lengkap = $validate['nama_mhs'];
            $skam->nim = $validate['nim'];
            $skam->nik = $validate['nik'] ?? null;
            $skam->tempat_lahir = $validate['tempat_lahir'];
            $skam->tanggal_lahir = $validate['tanggal_lahir'];
            $skam->prodi_mhs = $validate['prodi_mhs'];
            $skam->semester = $validate['semester'];
            // Set tahun_akademik from th_akademik if provided
            if (!empty($validate['th_akademik_id'])) {
                $thAkademik = ThAkademik::find($validate['th_akademik_id']);
                $skam->tahun_akademik = $thAkademik ? $thAkademik->nama . ' ' . $thAkademik->semester : $validate['tahun_akademik'];
            } else {
                $skam->tahun_akademik = $validate['tahun_akademik'] ?? '';
            }
            $skam->nama_ortu = $validate['nama_ortu'];
            $skam->nik_ortu = $validate['nik_ortu'] ?? null;
            $skam->nip_ortu = $validate['nip_ortu'] ?? null;
            $skam->alamat_ortu = $validate['alamat_ortu'];
            $skam->hp_ortu = $validate['hp_ortu'] ?? null;
            $skam->tanggal = $validate['tanggal'];
            $skam->user_id = Auth::user()->id;
            $skam->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skam->status = 'pending';
            $skam->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor       = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan Aktif Mahasiswa';
            $log->user_id       = Auth::user()->id;
            $log->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }

    public function show($id)
    {
        $skam = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id')
            ->select(
                'surat_keterangan_aktif_mahasiswa.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_aktif_mahasiswa.id', $id)
            ->first();

        if (!$skam) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $skam,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required|exists:prodi,id',
                'th_akademik_id' => 'nullable|exists:th_akademik,id',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'nik' => 'nullable|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'prodi_mhs' => 'required|string|max:255',
                'semester' => 'required|string|max:50',
                'tahun_akademik' => 'nullable|string|max:100',
                'nama_ortu' => 'required|string|max:255',
                'nik_ortu' => 'nullable|string|max:255',
                'nip_ortu' => 'nullable|string|max:255',
                'alamat_ortu' => 'required|string',
                'hp_ortu' => 'nullable|string|max:50',
                'tanggal' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $skam = SuratKeteranganAktifMahasiswa::find($id);
            if (!$skam) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Determine tahun_akademik from th_akademik if provided
            $tahunAkademik = $validate['tahun_akademik'] ?? $skam->tahun_akademik;
            if (!empty($request->th_akademik_id)) {
                $thAkademik = ThAkademik::find($request->th_akademik_id);
                $tahunAkademik = $thAkademik ? $thAkademik->nama . ' ' . $thAkademik->semester : $tahunAkademik;
            }

            // Map frontend fields (if different) to database fields
            $dataToUpdate = [
                'prodi_id'       => $validate['prodi_id'],
                'th_akademik_id' => $request->th_akademik_id ?? $skam->th_akademik_id,
                'nama_lengkap'   => $validate['nama_mhs'],
                'nim'            => $validate['nim'],
                'nik'            => $validate['nik'] ?? null,
                'tempat_lahir'   => $validate['tempat_lahir'],
                'tanggal_lahir'  => $validate['tanggal_lahir'],
                'prodi_mhs'      => $validate['prodi_mhs'],
                'semester'       => $validate['semester'],
                'tahun_akademik' => $tahunAkademik,
                'nama_ortu'      => $validate['nama_ortu'],
                'nik_ortu'       => $validate['nik_ortu'] ?? null,
                'nip_ortu'       => $validate['nip_ortu'] ?? null,
                'alamat_ortu'    => $validate['alamat_ortu'],
                'hp_ortu'        => $validate['hp_ortu'] ?? null,
                'tanggal'        => $validate['tanggal'],
                'jenis_kelamin'  => Auth::user()->jenis_kelamin,
                'user_id'        => Auth::user()->id,
            ];

            $skam->fill($dataToUpdate);
            $skam->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $skam = SuratKeteranganAktifMahasiswa::find($id);
            if (!$skam) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $skam->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }

    public function downloadPdf($id)
    {
        try {
            $data = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->leftJoin('th_akademik', 'th_akademik.id', '=', 'surat_keterangan_aktif_mahasiswa.th_akademik_id')
                ->select(
                    'surat_keterangan_aktif_mahasiswa.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'tanda_tangan.gambar as ttd',
                    'th_akademik.nama as th_akademik_nama',
                    'th_akademik.semester as th_akademik_semester',
                )
                ->where('surat_keterangan_aktif_mahasiswa.id', $id)
                ->first();

            Log::info($data);

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $stempelPath = base_path('../public_html/img/stempel.png');

            $stempelBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($stempelPath));

            $tddPath = base_path('../public_html/' . $data->ttd);
            $tddBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tddPath));

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            // Build tahun_akademik from th_akademik or fallback to stored value
            $tahunAkademik = $data->tahun_akademik;
            if ($data->th_akademik_nama) {
                $tahunAkademik = $data->th_akademik_nama . ' ' . $data->th_akademik_semester;
            }

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'nim' => $data->nim,
                'nik' => $data->nik,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'prodi_mhs' => $data->prodi_mhs,
                'semester' => $data->semester,
                'tahun_akademik' => $tahunAkademik,
                'nama_ortu' => $data->nama_ortu,
                'nik_ortu' => $data->nik_ortu,
                'nip_ortu' => $data->nip_ortu,
                'alamat_ortu' => $data->alamat_ortu,
                'hp_ortu' => $data->hp_ortu,
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_prodi' => $data->nama_prodi,
                'nama_kepala_prodi' => $data->nama_kepala_prodi,
                'nidn_kepala_prodi' => $data->nidn_kepala_prodi,
                'kopBase64' => $kopBase64,
                'stempel' => $stempelBase64,
                'ttd' => $tddBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_aktif', $pdfData)
                ->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_aktif_mahasiswa_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Aktif Mahasiswa';

            $googlePath = $data->prodi_mhs . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_mhs, SuratKeteranganAktifMahasiswa::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diunduh: ' . $th->getMessage()
            ]);
        }
    }

    public function getProdi()
    {
        try {
            $login = Auth::user()->prodi;
            if ($login) {
                $prodi = Prodi::where('id', $login->id)->first();
            } else {
                $prodi = Prodi::all();
            }
            return response()->json([
                'status' => true,
                'data' => $prodi
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diunduh'
            ]);
        }
    }
}
