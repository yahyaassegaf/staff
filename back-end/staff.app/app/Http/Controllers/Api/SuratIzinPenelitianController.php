<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratIzinPenelitian;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuratIzinPenelitianController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratIzinPenelitian::join('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id');

        $data->select(
            'surat_izin_penelitian.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_izin_penelitian.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_izin_penelitian.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_izin_penelitian.nim', 'like', "%{$search}%")
                    ->orWhere('surat_izin_penelitian.nama', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%");
            });
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'desc')
        );

        $data = $data->paginate($request->input('limit', 10));

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'prodi_id' => 'required',
                'nama' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'dari_tanggal' => 'required|date',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);
            $formattedNoSurat = SuratService::NoSuratIzinPenelitian($no_surat, $unit);

            $sip = new SuratIzinPenelitian();
            $sip->nomor = $formattedNoSurat;
            $sip->nama = $request->nama;
            $sip->nim = $request->nim;
            $sip->semester = $request->semester;
            $sip->dari_tanggal = $request->dari_tanggal;
            $sip->tanggal = $request->tanggal;
            $sip->prodi_id = $request->prodi_id;
            $sip->user_id = Auth::user()->id;
            $sip->jenis_kelamin = $request->jenis_kelamin;
            $sip->status = 'pending';
            $sip->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Izin Penelitian';
            $log->user_id = Auth::user()->id;
            $log->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $data = SuratIzinPenelitian::join('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id')
            ->select('surat_izin_penelitian.*', 'prodi.nama as nama_prodi')
            ->where('surat_izin_penelitian.id', $id)
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
            $request->validate([
                'prodi_id' => 'required',
                'nama' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'dari_tanggal' => 'required|date',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $sip = SuratIzinPenelitian::find($id);
            if (!$sip) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $sip->nama = $request->nama;
            $sip->nim = $request->nim;
            $sip->semester = $request->semester;
            $sip->dari_tanggal = $request->dari_tanggal;
            $sip->tanggal = $request->tanggal;
            $sip->prodi_id = $request->prodi_id;
            $sip->jenis_kelamin = $request->jenis_kelamin;
            $sip->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $sip = SuratIzinPenelitian::find($id);
            if (!$sip) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $sip->delete();
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
            $data = SuratIzinPenelitian::join('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id')
                ->join('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_izin_penelitian.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name'
                )
                ->where('surat_izin_penelitian.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $pdfData = [
                'nomor' => $data->nomor,
                'nama' => $data->nama,
                'nim' => $data->nim,
                'semester' => $data->semester,
                'dari_tanggal' => Carbon::parse($data->dari_tanggal)->translatedFormat('d F Y'),
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_kepala' => $data->nama_kepala,
                'nidn_kepala' => $data->nidn_kepala,
                'prodi_name' => $data->prodi_name,
                'fakultas_name' => $data->fakultas_name,
                'kopBase64' => $kopBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_izin_penelitian', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_izin_penelitian_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Izin Penelitian';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratIzinPenelitian::class);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal generate PDF']);
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
            return response()->json(['status' => true, 'data' => $prodi]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Gagal ambil data prodi']);
        }
    }
}
