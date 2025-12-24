<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratPernyataanVerifikasiNilai;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
            $validate = $request->validate([
                'prodi_id' => 'required|exists:prodi,id',
                'nama_penandatangan' => 'required|string|max:255',
                'niy' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'fakultas' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string|max:20',
                'drive_file_id' => 'nullable|string',
                'status' => 'nullable|in:pending,uploaded,failed',
            ]);

            $login = Auth::user()->prodi ? Auth::user()->prodi->alias : 'UMUM';
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);

            $noSurat = SuratService::NoSuratPernyataanMelakukanVerifikasiNilaiMahasiswa($no_surat, $unit);

            $surat = new SuratPernyataanVerifikasiNilai();
            $surat->nomor = $noSurat;
            $surat->nama_penandatangan = $request->nama_penandatangan;
            $surat->niy = $request->niy;
            $surat->jabatan = $request->jabatan;
            $surat->nama_mahasiswa = $request->nama_mhs;
            $surat->nim = $request->nim;
            $surat->prodi = $request->prodi;
            $surat->fakultas = $request->fakultas;
            $surat->tanggal = $request->tanggal;
            $surat->prodi_id = $request->prodi_id;
            $surat->jenis_kelamin = $request->jenis_kelamin;
            $surat->user_id = Auth::user()->id;
            $surat->drive_file_id = $request->drive_file_id;
            $surat->status = $request->status ?? 'pending';
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
            ->select(
                'surat_pernyataan_verifikasi_nilai.*',
                'prodi.nama as nama_prodi'
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
            $validate = $request->validate([
                'prodi_id' => 'required|exists:prodi,id',
                'nama_penandatangan' => 'required|string|max:255',
                'niy' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'fakultas' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string|max:20',
                'drive_file_id' => 'nullable|string',
                'status' => 'nullable|in:pending,uploaded,failed',
            ]);

            $surat = SuratPernyataanVerifikasiNilai::find($id);
            if (!$surat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $surat->nama_penandatangan = $request->nama_penandatangan;
            $surat->niy = $request->niy;
            $surat->jabatan = $request->jabatan;
            $surat->nama_mahasiswa = $request->nama_mhs;
            $surat->nim = $request->nim;
            $surat->prodi = $request->prodi;
            $surat->fakultas = $request->fakultas;
            $surat->tanggal = $request->tanggal;
            $surat->prodi_id = $request->prodi_id;
            $surat->jenis_kelamin = $request->jenis_kelamin;
            $surat->drive_file_id = $request->drive_file_id;
            $surat->status = $request->status ?? $surat->status;

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
                ->select(
                    'surat_pernyataan_verifikasi_nilai.*',
                    'prodi.nama as nama_prodi'
                )
                ->where('surat_pernyataan_verifikasi_nilai.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $pdfData = [
                'nomor' => $data->nomor,
                'nama_penandatangan' => $data->nama_penandatangan,
                'niy' => $data->niy,
                'jabatan' => $data->jabatan,
                'nama_mahasiswa' => $data->nama_mahasiswa,
                'nim' => $data->nim,
                'prodi' => $data->prodi,
                'fakultas' => $data->fakultas,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'jenis_kelamin' => $data->jenis_kelamin,
                'kopBase64' => $kopBase64,
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
            UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratPernyataanVerifikasiNilai::class);

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
