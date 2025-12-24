<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratTugas;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuratTugasController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratTugas::join('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id');

        $data->select(
            'surat_tugas.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_tugas.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_tugas.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_tugas.nim_nik', 'like', "%{$search}%")
                    ->orWhere('surat_tugas.nama_mhs', 'like', "%{$search}%")
                    ->orWhere('surat_tugas.nama_dosen', 'like', "%{$search}%")
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
                'nama_dosen' => 'required|string|max:255',
                'alamat_dosen' => 'required|string|max:255',
                'tugas_dosen' => 'required|string|max:255',
                'tugasnya' => 'required|string',
                'nama_mhs' => 'required|string|max:255',
                'nim_nik' => 'required|string|max:255',
                'fakultas_prodi' => 'required|string|max:255',
                'judul_skripsi' => 'required|string',
                'masa_penugasan' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);
            $formattedNoSurat = SuratService::NoSuratTugas($no_surat, $unit);

            $st = new SuratTugas();
            $st->nomor = $formattedNoSurat;
            $st->nama_dosen = $request->nama_dosen;
            $st->alamat_dosen = $request->alamat_dosen;
            $st->tugas_dosen = $request->tugas_dosen;
            $st->tugasnya = $request->tugasnya;
            $st->nama_mhs = $request->nama_mhs;
            $st->nim_nik = $request->nim_nik;
            $st->fakultas_prodi = $request->fakultas_prodi;
            $st->judul_skripsi = $request->judul_skripsi;
            $st->masa_penugasan = $request->masa_penugasan;
            $st->tanggal = $request->tanggal;
            $st->user_id = Auth::user()->id;
            $st->prodi_id = $request->prodi_id;
            $st->jenis_kelamin = $request->jenis_kelamin;
            $st->status = 'pending';
            $st->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Tugas';
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
        $data = SuratTugas::join('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id')
            ->select('surat_tugas.*', 'prodi.nama as nama_prodi')
            ->where('surat_tugas.id', $id)
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
                'nama_dosen' => 'required|string|max:255',
                'alamat_dosen' => 'required|string|max:255',
                'tugas_dosen' => 'required|string|max:255',
                'tugasnya' => 'required|string',
                'nama_mhs' => 'required|string|max:255',
                'nim_nik' => 'required|string|max:255',
                'fakultas_prodi' => 'required|string|max:255',
                'judul_skripsi' => 'required|string',
                'masa_penugasan' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $st = SuratTugas::find($id);
            if (!$st) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $st->nama_dosen = $request->nama_dosen;
            $st->alamat_dosen = $request->alamat_dosen;
            $st->tugas_dosen = $request->tugas_dosen;
            $st->tugasnya = $request->tugasnya;
            $st->nama_mhs = $request->nama_mhs;
            $st->nim_nik = $request->nim_nik;
            $st->fakultas_prodi = $request->fakultas_prodi;
            $st->judul_skripsi = $request->judul_skripsi;
            $st->masa_penugasan = $request->masa_penugasan;
            $st->tanggal = $request->tanggal;
            $st->prodi_id = $request->prodi_id;
            $st->jenis_kelamin = $request->jenis_kelamin;
            $st->save();

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
            $st = SuratTugas::find($id);
            if (!$st) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $st->delete();
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
            $data = SuratTugas::join('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id')
                ->join('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_tugas.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name'
                )
                ->where('surat_tugas.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $pdfData = [
                'nomor' => $data->nomor,
                'nama_dosen' => $data->nama_dosen,
                'alamat_dosen' => $data->alamat_dosen,
                'tugas_dosen' => $data->tugas_dosen,
                'tugasnya' => $data->tugasnya,
                'nama_mhs' => $data->nama_mhs,
                'nim_nik' => $data->nim_nik,
                'fakultas_prodi' => $data->fakultas_prodi,
                'judul_skripsi' => $data->judul_skripsi,
                'masa_penugasan' => $data->masa_penugasan,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_kepala' => $data->nama_kepala,
                'nidn_kepala' => $data->nidn_kepala,
                'kopBase64' => $kopBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_tugas', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_tugas_' . $data->nim_nik . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Tugas';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratTugas::class);

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
