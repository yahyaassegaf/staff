<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeterangan;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan.jenis_kelamin', 'P');
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
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'periode_bulan' => 'required|string|max:255',
                'alasan' => 'required|string',
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

            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);
            $formattedNoSurat = SuratService::NoSuratKeterangan($no_surat, $unit);

            $sk = new SuratKeterangan();
            $sk->nomor = $formattedNoSurat;
            $sk->nama_mahasiswa = $validate['nama_mhs'];
            $sk->nim = $validate['nim'];
            $sk->prodi = $validate['prodi'];
            $sk->periode_bulan = $validate['periode_bulan'];
            $sk->tanda_tangan_id = $validate['tanda_tangan_id'];
            $sk->alasan = $validate['alasan'];
            $sk->tanggal = $validate['tanggal'];
            $sk->user_id = Auth::user()->id;
            $sk->prodi_id = $validate['prodi_id'];
            $sk->jenis_kelamin = Auth::user()->jenis_kelamin;
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
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan.tanda_tangan_id')
            ->select(
                'surat_keterangan.*',
                'prodi.nama as nama_prodi',
                'tanda_tangan.nama as nama_ttd'
            )
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
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'periode_bulan' => 'required|string|max:255',
                'alasan' => 'required|string',
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

            $sk = SuratKeterangan::find($id);
            if (!$sk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $sk->fill([
                'nama_mahasiswa'   => $validate['nama_mhs'],
                'nim'              => $validate['nim'],
                'prodi'            => $validate['prodi'],
                'periode_bulan'    => $validate['periode_bulan'],
                'tanda_tangan_id'  => $validate['tanda_tangan_id'],
                'alasan'           => $validate['alasan'],
                'tanggal'          => $validate['tanggal'],
                'prodi_id'         => $validate['prodi_id'],
                'jenis_kelamin'    => Auth::user()->jenis_kelamin,
            ]);
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
            $data = SuratKeterangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan.tanda_tangan_id')
                ->select(
                    'surat_keterangan.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    'tanda_tangan.gambar as ttd',
                    'tanda_tangan.nama as nama_staff',
                )
                ->where('surat_keterangan.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
            $tddPath = base_path('../public_html/' . $data->ttd);

            $tddBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tddPath));
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));

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
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64
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

            $googlePath = $data->prodi_name . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratKeterangan::class);
            }

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
