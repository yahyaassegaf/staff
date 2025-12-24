<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganTransfer;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SuratKeteranganTransferController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganTransfer::join('prodi', 'prodi.id', '=', 'surat_keterangan_transfer.prodi_id');

        $data->select(
            'surat_keterangan_transfer.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan_transfer.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan_transfer.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_keterangan_transfer.nim', 'like', "%{$search}%")
                    ->orWhere('surat_keterangan_transfer.nama', 'like', "%{$search}%")
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
                'dekan' => 'required|string|max:255',
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jurusan_prodi' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'tahun_akademik' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);
            $formattedNoSurat = SuratService::NoSuratKeteranganTransfer($no_surat, $unit);

            $skt = new SuratKeteranganTransfer();
            $skt->nomor = $formattedNoSurat;
            $skt->dekan = $request->dekan;
            $skt->nama = $request->nama;
            $skt->tanggal_lahir = $request->tanggal_lahir;
            $skt->nim = $request->nim;
            $skt->jurusan_prodi = $request->jurusan_prodi;
            $skt->semester = $request->semester;
            $skt->tahun_akademik = $request->tahun_akademik;
            $skt->tanggal = $request->tanggal;
            $skt->user_id = Auth::user()->id;
            $skt->prodi_id = $request->prodi_id;
            $skt->jenis_kelamin = $request->jenis_kelamin;
            $skt->status = 'pending';
            $skt->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Keterangan Transfer';
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
        $data = SuratKeteranganTransfer::join('prodi', 'prodi.id', '=', 'surat_keterangan_transfer.prodi_id')
            ->select('surat_keterangan_transfer.*', 'prodi.nama as nama_prodi')
            ->where('surat_keterangan_transfer.id', $id)
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
                'dekan' => 'required|string|max:255',
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jurusan_prodi' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'tahun_akademik' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            $skt = SuratKeteranganTransfer::find($id);
            if (!$skt) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $skt->dekan = $request->dekan;
            $skt->nama = $request->nama;
            $skt->tanggal_lahir = $request->tanggal_lahir;
            $skt->nim = $request->nim;
            $skt->jurusan_prodi = $request->jurusan_prodi;
            $skt->semester = $request->semester;
            $skt->tahun_akademik = $request->tahun_akademik;
            $skt->tanggal = $request->tanggal;
            $skt->prodi_id = $request->prodi_id;
            $skt->jenis_kelamin = $request->jenis_kelamin;
            $skt->save();

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
            $skt = SuratKeteranganTransfer::find($id);
            if (!$skt) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $skt->delete();
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
            $data = SuratKeteranganTransfer::join('prodi', 'prodi.id', '=', 'surat_keterangan_transfer.prodi_id')
                ->join('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_keterangan_transfer.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name'
                )
                ->where('surat_keterangan_transfer.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $pdfData = [
                'nomor' => $data->nomor,
                'dekan' => $data->dekan,
                'nama' => $data->nama,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'jurusan_prodi' => $data->jurusan_prodi,
                'semester' => $data->semester,
                'tahun_akademik' => $data->tahun_akademik,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_kepala' => $data->nama_kepala,
                'nidn_kepala' => $data->nidn_kepala,
                'kopBase64' => $kopBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_keterangan_transfer', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_keterangan_transfer_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Transfer';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratKeteranganTransfer::class);

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
