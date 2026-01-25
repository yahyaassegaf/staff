<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratPernyataanVerifikasiNilai;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratPernyataanVerifikasiNilaiController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id');

        $data->select(
            'surat_pernyataan_verifikasi_nilai.*',
            'prodi.nama as nama_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_pernyataan_verifikasi_nilai.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_pernyataan_verifikasi_nilai.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_mahasiswa', 'like', "%{$search}%")
                    ->orWhere('surat_pernyataan_verifikasi_nilai.prodi', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_pernyataan_verifikasi_nilai.jenis_kelamin', 'L');
        } else {
            $data->where('surat_pernyataan_verifikasi_nilai.jenis_kelamin', 'P');
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
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
                'niy' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'fakultas' => 'required|string|max:255',
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

            $login = Auth::user()->prodi ? Auth::user()->prodi->alias : 'UMUM';
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);

            $noSurat = SuratService::NoSuratPernyataanMelakukanVerifikasiNilaiMahasiswa($no_surat, $unit);

            $surat = new SuratPernyataanVerifikasiNilai();
            $surat->nomor = $noSurat;
            $surat->tanda_tangan_id = $validate['tanda_tangan_id'];
            $surat->niy = $validate['niy'];
            $surat->jabatan = $validate['jabatan'];
            $surat->nama_mahasiswa = $validate['nama_mhs'];
            $surat->nim = $validate['nim'];
            $surat->prodi_mhs = $validate['prodi'];
            $surat->fakultas = $validate['fakultas'];
            $surat->tanggal = $validate['tanggal'];
            $surat->prodi_id = $validate['prodi_id'];
            $surat->jenis_kelamin = Auth::user()->jenis_kelamin;
            $surat->user_id = Auth::user()->id;
            $surat->status = 'pending';
            $surat->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor       = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Pernyataan Verifikasi Nilai';
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
                'message' => 'Data gagal ditambahkan: ' . $th->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_pernyataan_verifikasi_nilai.tanda_tangan_id')
            ->select(
                'surat_pernyataan_verifikasi_nilai.*',
                'prodi.nama as nama_prodi',
                'tanda_tangan.nama as nama_ttd'
            )
            ->where('surat_pernyataan_verifikasi_nilai.id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        try {

            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required|exists:prodi,id',
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
                'niy' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'fakultas' => 'required|string|max:255',
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

            $surat = SuratPernyataanVerifikasiNilai::find($id);
            if (!$surat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $surat->tanda_tangan_id = $validate['tanda_tangan_id'];
            $surat->niy = $validate['niy'];
            $surat->jabatan = $validate['jabatan'];
            $surat->nama_mahasiswa = $validate['nama_mhs'];
            $surat->nim = $validate['nim'];
            $surat->prodi_mhs = $validate['prodi'];
            $surat->fakultas = $validate['fakultas'];
            $surat->tanggal = $validate['tanggal'];
            $surat->prodi_id = $validate['prodi_id'];
            $surat->jenis_kelamin = Auth::user()->jenis_kelamin;
            $surat->save();

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
            $surat = SuratPernyataanVerifikasiNilai::find($id);
            if (!$surat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $surat->delete();

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
            $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id')
                ->leftJoin('users', 'users.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_pernyataan_verifikasi_nilai.tanda_tangan_id')
                ->select(
                    'surat_pernyataan_verifikasi_nilai.*',
                    'users.name as nama_staff',
                    'prodi.nama as nama_prodi',
                    'tanda_tangan.gambar as ttd',
                    'tanda_tangan.nama as nama_penandatangan',
                    'fakultas.nama as nama_fakultas'
                )
                ->where('surat_pernyataan_verifikasi_nilai.id', $id)
                ->first();
            Log::info($data);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $tddPath = base_path('../public_html/' . $data->ttd);

            $nama = strtolower($data->nama_mahasiswa);
            $nim = strtolower($data->nim);

            $tddBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tddPath));

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));

            $jabatan = 'Staff Program Studi ' . ucwords($data->nama_prodi) . ' Fakultas ' . ucwords($data->nama_fakultas);
            $staff = 'Staff Prodi ' . ucwords($data->nama_prodi);
            $pdfData = [
                'nomor' => $data->nomor,
                'nama_penandatangan' => $data->nama_penandatangan,
                'niy' => $data->niy,
                'jabatan' => $jabatan,
                'staff' => $staff,
                'nama_mahasiswa' => $nama,
                'nim' => $nim,
                'prodi' => $data->nama_prodi,
                'fakultas' => $data->nama_fakultas,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'jenis_kelamin' => $data->jenis_kelamin,
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_pernyataan_verifikasi_nilai', $pdfData)
                ->setPaper('a4', 'portrait');

            $fileName = 'surat_pernyataan_verifikasi_nilai_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Pernyataan Verifikasi Nilai';

            $googlePath = $data->prodi_mhs . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_mhs, SuratPernyataanVerifikasiNilai::class);
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
                $prodi = Prodi::where('id', $login->id)->get();
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
