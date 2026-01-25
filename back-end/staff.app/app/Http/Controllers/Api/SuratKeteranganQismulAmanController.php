<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganQismulAman;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganQismulAmanController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganQismulAman::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_qismul_aman.prodi_id');

        $data->select(
            'surat_keterangan_qismul_aman.id as id',
            'surat_keterangan_qismul_aman.nomor_surat',
            'surat_keterangan_qismul_aman.ketua',
            'surat_keterangan_qismul_aman.nama_lengkap',
            'surat_keterangan_qismul_aman.tempat_lahir',
            'surat_keterangan_qismul_aman.tanggal_lahir',
            'surat_keterangan_qismul_aman.nim',
            'surat_keterangan_qismul_aman.prodi_id',
            'surat_keterangan_qismul_aman.jenis_kelamin',
            'surat_keterangan_qismul_aman.prodi_mhs',
            'surat_keterangan_qismul_aman.alamat_rumah',
            'surat_keterangan_qismul_aman.kelas_pondok',
            'surat_keterangan_qismul_aman.tanggal_berlaku_dari',
            'surat_keterangan_qismul_aman.tanggal_berlaku_sampai',
            'surat_keterangan_qismul_aman.tanggal',
            'surat_keterangan_qismul_aman.drive_file_id',
            'surat_keterangan_qismul_aman.status',
            'surat_keterangan_qismul_aman.created_at',
            'surat_keterangan_qismul_aman.updated_at',
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
                    ->orWhere('prodi_mhs', 'like', "%{$search}%")
                    ->orWhere('kelas_pondok', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_qismul_aman.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_qismul_aman.jenis_kelamin', 'P');
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
                'prodi_id' => 'nullable|exists:prodi,id',
                'ketua' => 'nullable|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jenis_kelamin' => 'required|string|max:50',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal_berlaku_dari' => 'required|date',
                'tanggal_berlaku_sampai' => 'required|date',
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

            $noSurat = SuratService::NoSuratKeteranganQismulAman($no_surat, $unit);

            $skqa = new SuratKeteranganQismulAman();
            $skqa->nomor_surat = $noSurat;
            $skqa->ketua = $validate['ketua'] ?? null;
            $skqa->nama_lengkap = $validate['nama_mhs'];
            $skqa->tempat_lahir = $validate['tempat_lahir'];
            $skqa->tanggal_lahir = $validate['tanggal_lahir'];
            $skqa->nim = $validate['nim'];
            $skqa->prodi_id = $validate['prodi_id'] ?? null;
            $skqa->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skqa->prodi_mhs = $validate['prodi_mhs'];
            $skqa->alamat_rumah = $validate['alamat_rumah'];
            $skqa->kelas_pondok = $validate['kelas_pondok'];
            $skqa->tanggal_berlaku_dari = $validate['tanggal_berlaku_dari'];
            $skqa->tanggal_berlaku_sampai = $validate['tanggal_berlaku_sampai'];
            $skqa->tanggal = $validate['tanggal'];
            $skqa->user_id = Auth::user()->id;
            $skqa->status = $validate['status'] ?? 'pending';
            $skqa->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor       = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan Qismul Aman';
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
        $skqa = SuratKeteranganQismulAman::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_qismul_aman.prodi_id')
            ->select(
                'surat_keterangan_qismul_aman.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_qismul_aman.id', $id)
            ->first();

        if (!$skqa) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $skqa,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'nullable|exists:prodi,id',
                'ketua' => 'nullable|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jenis_kelamin' => 'required|string|max:50',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal_berlaku_dari' => 'required|date',
                'tanggal_berlaku_sampai' => 'required|date',
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

            $skqa = SuratKeteranganQismulAman::find($id);
            if (!$skqa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $skqa->prodi_id = $validate['prodi_id'] ?? $skqa->prodi_id;
            $skqa->ketua = $validate['ketua'] ?? null;
            $skqa->nama_lengkap = $validate['nama_mhs'];
            $skqa->tempat_lahir = $validate['tempat_lahir'];
            $skqa->tanggal_lahir = $validate['tanggal_lahir'];
            $skqa->nim = $validate['nim'];
            $skqa->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skqa->prodi_mhs = $validate['prodi_mhs'];
            $skqa->alamat_rumah = $validate['alamat_rumah'];
            $skqa->kelas_pondok = $validate['kelas_pondok'];
            $skqa->tanggal_berlaku_dari = $validate['tanggal_berlaku_dari'];
            $skqa->tanggal_berlaku_sampai = $validate['tanggal_berlaku_sampai'];
            $skqa->tanggal = $validate['tanggal'];
            // $skqa->drive_file_id = $validate['drive_file_id'] ?? $skqa->drive_file_id;
            // $skqa->status = $validate['status'] ?? $skqa->status;
            $skqa->save();

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
            $skqa = SuratKeteranganQismulAman::find($id);
            if (!$skqa) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $skqa->delete();

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
            $data = SuratKeteranganQismulAman::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_qismul_aman.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_qismul_aman.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_qismul_aman.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $tddPath = base_path('../public_html/' . $data->ttd);

            $tddBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tddPath));
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = '';
            if (file_exists($kopPath)) {
                $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));
            }

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'ketua' => $data->ketua,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'prodi' => $data->prodi_mhs,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'jenis_kelamin' => $data->jenis_kelamin,
                'tanggal_berlaku_dari' => Carbon::parse($data->tanggal_berlaku_dari)->translatedFormat('d F Y'),
                'tanggal_berlaku_sampai' => Carbon::parse($data->tanggal_berlaku_sampai)->translatedFormat('d F Y'),
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
            ];

            $pdf = Pdf::loadView('pdf.surat_qismul_aman', $pdfData)
                ->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_qismul_aman_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Qismul Aman';

            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganQismulAman::class);
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
