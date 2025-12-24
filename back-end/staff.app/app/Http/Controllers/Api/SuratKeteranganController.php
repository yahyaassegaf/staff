<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeterangan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuratKeteranganController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeterangan::join('prodi', 'prodi.id', '=', 'surat_keterangan.prodi_id');

        $data->select(
            'surat_keterangan.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_keterangan.nim', 'like', "%{$search}%")
                    ->orWhere('surat_keterangan.nama_mahasiswa', 'like', "%{$search}%")
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
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'periode_bulan' => 'required|string|max:255',
                'nama_staff' => 'required|string|max:255',
                'alasan' => 'required|string',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);
            $formattedNoSurat = SuratService::NoSuratKeterangan($no_surat, $unit);

            $sk = new SuratKeterangan();
            $sk->nomor = $formattedNoSurat;
            $sk->nama_mahasiswa = $request->nama_mhs;
            $sk->nim = $request->nim;
            $sk->prodi = $request->prodi;
            $sk->periode_bulan = $request->periode_bulan;
            $sk->nama_staff = $request->nama_staff;
            $sk->alasan = $request->alasan;
            $sk->tanggal = $request->tanggal;
            $sk->user_id = Auth::user()->id;
            $sk->prodi_id = $request->prodi_id;
            $sk->jenis_kelamin = $request->jenis_kelamin;
            $sk->status = 'pending';
            $sk->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Keterangan';
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
        $data = SuratKeterangan::join('prodi', 'prodi.id', '=', 'surat_keterangan.prodi_id')
            ->select('surat_keterangan.*', 'prodi.nama as nama_prodi')
            ->where('surat_keterangan.id', $id)
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
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'periode_bulan' => 'required|string|max:255',
                'nama_staff' => 'required|string|max:255',
                'alasan' => 'required|string',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $sk = SuratKeterangan::find($id);
            if (!$sk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $sk->nama_mahasiswa = $request->nama_mhs;
            $sk->nim = $request->nim;
            $sk->prodi = $request->prodi;
            $sk->periode_bulan = $request->periode_bulan;
            $sk->nama_staff = $request->nama_staff;
            $sk->alasan = $request->alasan;
            $sk->tanggal = $request->tanggal;
            $sk->prodi_id = $request->prodi_id;
            $sk->jenis_kelamin = $request->jenis_kelamin;
            $sk->save();

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
            $sk = SuratKeterangan::find($id);
            if (!$sk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $sk->delete();
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
            $data = SuratKeterangan::join('prodi', 'prodi.id', '=', 'surat_keterangan.prodi_id')
                ->join('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_keterangan.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name'
                )
                ->where('surat_keterangan.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $pdfData = [
                'nomor' => $data->nomor,
                'nama_mahasiswa' => $data->nama_mahasiswa,
                'nim' => $data->nim,
                'prodi' => $data->prodi,
                'periode_bulan' => $data->periode_bulan,
                'nama_staff' => $data->nama_staff,
                'alasan' => $data->alasan,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'jenis_kelamin' => $data->jenis_kelamin,
                'nama_kepala' => $data->nama_kepala,
                'nidn_kepala' => $data->nidn_kepala,
                'kopBase64' => $kopBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_keterangan', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_keterangan_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratKeterangan::class);

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
