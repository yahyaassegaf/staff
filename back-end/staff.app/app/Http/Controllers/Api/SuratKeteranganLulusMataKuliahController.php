<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganLulusMataKuliah;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuratKeteranganLulusMataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganLulusMataKuliah::join('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id');

        $data->select(
            'surat_keterangan_lulus_mata_kuliah.id as id',
            'surat_keterangan_lulus_mata_kuliah.nomor_surat',
            'surat_keterangan_lulus_mata_kuliah.prodi_id',
            'surat_keterangan_lulus_mata_kuliah.nama_lengkap',
            'surat_keterangan_lulus_mata_kuliah.tempat_lahir',
            'surat_keterangan_lulus_mata_kuliah.tanggal_lahir',
            'surat_keterangan_lulus_mata_kuliah.nim',
            'surat_keterangan_lulus_mata_kuliah.prodi_mahasiswa',
            'surat_keterangan_lulus_mata_kuliah.alamat_rumah',
            'surat_keterangan_lulus_mata_kuliah.kelas_pondok',
            'surat_keterangan_lulus_mata_kuliah.tanggal',
            'surat_keterangan_lulus_mata_kuliah.drive_file_id',
            'surat_keterangan_lulus_mata_kuliah.status',
            'surat_keterangan_lulus_mata_kuliah.created_at',
            'surat_keterangan_lulus_mata_kuliah.updated_at',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%")
                    ->orWhere('prodi.alias', 'like', "%{$search}%")
                    ->orWhere('kelas_pondok', 'like', "%{$search}%");
            });
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'asc')
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
            $validate = $request->validate([
                'prodi_id' => 'required',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal' => 'nullable|date',
                'drive_file_id' => 'nullable|string',
                'status' => 'nullable|in:pending,uploaded,failed',
            ]);
            // Generate nomor surat
            // $tahun = date('Y');
            // $count = SuratKeteranganLulusMataKuliah::whereYear('created_at', $tahun)->count() + 1;
            // $nomorSurat = sprintf("%03d/SKLMK/FTI/%s", $count, $tahun);


            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            // Format nomor surat: 001/SK.UKD/K.FTI/2025
            // K + Alias Prodi (KFTI)
            $tahun = date('Y');
            $unit = 'K.' . strtoupper($login);

            $noSurat = SuratService::NoSuratKeteranganLulusMataKuliah($no_surat, $unit);

            $sklmk = new SuratKeteranganLulusMataKuliah();
            $sklmk->nomor_surat = $noSurat;
            $sklmk->prodi_id = $request->prodi_id;
            $sklmk->nama_lengkap = $request->nama_mhs;
            $sklmk->tempat_lahir = $request->tempat_lahir;
            $sklmk->tanggal_lahir = $request->tanggal_lahir;
            $sklmk->nim = $request->nim;
            $sklmk->prodi_mahasiswa = $request->prodi_mhs;
            $sklmk->alamat_rumah = $request->alamat_rumah;
            $sklmk->kelas_pondok = $request->kelas_pondok;
            $sklmk->tanggal = $request->tanggal;
            $sklmk->user_id = Auth::user()->id;
            $sklmk->drive_file_id = $request->drive_file_id;
            $sklmk->status = $request->status ?? 'pending';
            $sklmk->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor       = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat = 'Surat Keterangan Lulus Mata Kuliah';
            $log->user_id = Auth::user()->id;
            $log->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }

    public function show($id)
    {
        $sklmk = SuratKeteranganLulusMataKuliah::join('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
            ->select(
                'surat_keterangan_lulus_mata_kuliah.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_lulus_mata_kuliah.id', $id)
            ->first();

        if (!$sklmk) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        Log::info($sklmk);

        return response()->json([
            'status' => true,
            'data' => $sklmk,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validate = $request->validate([
                'prodi_id' => 'sometimes|exists:prodi,id',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'drive_file_id' => 'nullable|string',
                'status' => 'nullable|in:pending,uploaded,failed',
            ]);

            $sklmk = SuratKeteranganLulusMataKuliah::find($id);
            if (!$sklmk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            if (array_key_exists('prodi_id', $validate)) {
                $sklmk->prodi_id = $validate['prodi_id'];
            }
            if (array_key_exists('nama_mhs', $validate)) {
                $sklmk->nama_lengkap = $validate['nama_mhs'];
            }
            if (array_key_exists('tempat_lahir', $validate)) {
                $sklmk->tempat_lahir = $validate['tempat_lahir'];
            }
            if (array_key_exists('tanggal_lahir', $validate)) {
                $sklmk->tanggal_lahir = $validate['tanggal_lahir'];
            }
            if (array_key_exists('nim', $validate)) {
                $sklmk->nim = $validate['nim'];
            }
            if (array_key_exists('prodi_mhs', $validate)) {
                $sklmk->prodi_mahasiswa = $validate['prodi_mhs'];
            }
            if (array_key_exists('alamat_rumah', $validate)) {
                $sklmk->alamat_rumah = $validate['alamat_rumah'];
            }
            if (array_key_exists('kelas_pondok', $validate)) {
                $sklmk->kelas_pondok = $validate['kelas_pondok'];
            }
            if (array_key_exists('tanggal', $validate)) {
                $sklmk->tanggal = $validate['tanggal'];
            }
            if (array_key_exists('drive_file_id', $validate)) {
                $sklmk->drive_file_id = $validate['drive_file_id'];
            }
            if (array_key_exists('status', $validate)) {
                $sklmk->status = $validate['status'];
            }

            $sklmk->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $sklmk = SuratKeteranganLulusMataKuliah::find($id);
            if (!$sklmk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $sklmk->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }
    public function downloadPdf($id)
    {
        try {
            $data = SuratKeteranganLulusMataKuliah::join('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
                ->join('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_keterangan_lulus_mata_kuliah.*',
                    'prodi.nama as nama_prodi',
                    'fakultas.nama as fakultas',
                    'prodi.alias as alias_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi'
                )
                ->where('surat_keterangan_lulus_mata_kuliah.id', $id)
                ->first();
            Log::info($data);
            if (!$data) {
                Log::info('data kosong');
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            Log::info('data ada');
            $kopPath = base_path('../public_html/img/kop.jpg');
            Log::info($kopPath);
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));
            Log::info($kopBase64);
            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'fakultas' => $data->fakultas,
                'prodi' => $data->nama_prodi,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'alias_prodi' => $data->alias_prodi,
                'nama_kepala_prodi' => $data->nama_kepala_prodi,
                'nidn_kepala_prodi' => $data->nidn_kepala_prodi,
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_penandatangan' => $data->koor_komprehensif, // Assuming ttd field contains the name
                'jabatan_penandatangan' => 'Ketua / Koordinator Komprehensip',
                'kopBase64' => $kopBase64, // Static title based on blade template
            ];
            Log::info($pdfData);
            // Load view pdf.komprehensif created by user
            $pdf = Pdf::loadView('pdf.v_surat_keterangan_lulus_mata_kuliah', $pdfData)
                ->setPaper('a4', 'portrait');
            Log::info('pdf');
            $fileName = 'surat_keterangan_lulus_mata_kuliah_' . $data->nim . '_' . $data->alias_prodi . '.pdf';
            Log::info($fileName);
            $directory = public_path('pdf/');
            Log::info($directory);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }


            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Lulus Mata Kuliah';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganLulusMataKuliah::class);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diunduh'
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
