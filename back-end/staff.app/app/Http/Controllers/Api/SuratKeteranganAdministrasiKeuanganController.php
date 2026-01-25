<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganAdministrasiKeuangan;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganAdministrasiKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id');

        $data->select(
            'surat_keterangan_administrasi_keuangan.*',
            'prodi.nama as nama_prodi'
        );

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%")
                    ->orWhere('kelas_pondok', 'like', "%{$search}%");
            });
        }

        if ($request->filled('prodi_id')) {
            $data->where('prodi_id', $request->prodi_id);
        }

        $login  = Auth::user()->prodi;
        if ($login) {
            $data->where('prodi_id', $login->id);
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_administrasi_keuangan.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_administrasi_keuangan.jenis_kelamin', 'P');
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
            $validator = Validator::make($request->all(), [
                'prodi_id'      => 'nullable',
                'nama_mhs'      => 'required|string|max:255',
                'tempat_lahir'  => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim'           => 'required|string|max:255',
                'prodi_mhs'     => 'required|string|max:255',
                'alamat_rumah'  => 'required|string',
                'kelas_pondok'  => 'required|string|max:255',
                'tanggal'       => 'required|date',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $loginArgs = $validate['prodi_mhs'];
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            Log::info($no);
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);
            Log::info($no_surat);

            $unit = 'BAK';

            $noSurat = SuratService::NoSuratKeteranganAdministrasiKeuangan($no_surat, $unit);

            $data = new SuratKeteranganAdministrasiKeuangan();
            $data->nomor_surat  = $noSurat;
            $data->prodi_id     = $validate['prodi_id'];
            $data->nama_lengkap = $validate['nama_mhs'];
            $data->tempat_lahir = $validate['tempat_lahir'];
            $data->tanggal_lahir = $validate['tanggal_lahir'];
            $data->nim          = $validate['nim'];
            $data->prodi_mhs    = $validate['prodi_mhs'];
            $data->alamat_rumah = $validate['alamat_rumah'];
            $data->kelas_pondok = $validate['kelas_pondok'];
            $data->tanggal      = $validate['tanggal'];
            $data->tanda_tangan_id = $validate['tanda_tangan_id'] ?? null;
            // Set kepala_biro from tanda_tangan nama if provided
            if (!empty($validate['tanda_tangan_id'])) {
                $tandaTangan = TandaTangan::find($validate['tanda_tangan_id']);
                $data->kepala_biro = $tandaTangan?->nama;
            }
            $data->jenis_kelamin = Auth::user()->jenis_kelamin;
            $data->user_id      = Auth::user()->id;
            $data->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor      = $no_surat;
            $Nomor->user_id    = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat = 'Surat Keterangan Administrasi Keuangan';
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
        $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
            ->select(
                'surat_keterangan_administrasi_keuangan.*',
                'prodi.nama as nama_prodi'
            )
            ->where('surat_keterangan_administrasi_keuangan.id', $id)
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
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id'      => 'required|exists:prodi,id',
                'nama_mhs'      => 'required|string|max:255',
                'tempat_lahir'  => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim'           => 'required|string|max:255',
                'prodi_mhs'     => 'required|string|max:255',
                'alamat_rumah'  => 'required|string',
                'kelas_pondok'  => 'required|string|max:255',
                'tanggal'       => 'required|date',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $data = SuratKeteranganAdministrasiKeuangan::find($id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }


            $data->prodi_id = $validate['prodi_id'];
            $data->nama_lengkap = $validate['nama_mhs'];
            $data->tempat_lahir = $validate['tempat_lahir'];
            $data->tanggal_lahir = $validate['tanggal_lahir'];
            $data->nim = $validate['nim'];
            $data->prodi_mhs = $validate['prodi_mhs'];
            $data->alamat_rumah = $validate['alamat_rumah'];
            $data->kelas_pondok = $validate['kelas_pondok'];
            $data->tanggal = $validate['tanggal'];
            if (array_key_exists('tanda_tangan_id', $validate)) {
                $data->tanda_tangan_id = $validate['tanda_tangan_id'];
                if (!empty($validate['tanda_tangan_id'])) {
                    $tandaTangan = TandaTangan::find($validate['tanda_tangan_id']);
                    $data->kepala_biro = $tandaTangan?->nama;
                }
            }
            $data->jenis_kelamin = Auth::user()->jenis_kelamin;
            $data->user_id      = Auth::user()->id;
            $data->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $data = SuratKeteranganAdministrasiKeuangan::find($id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $data->delete();

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
        Log::info('masuk.....');
        Log::info($id);
        try {
            $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_administrasi_keuangan.tanda_tangan_id')
                ->select(
                    'surat_keterangan_administrasi_keuangan.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'fakultas.nama as fakultas',
                    'tanda_tangan.nama as nama_ttd',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_administrasi_keuangan.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            Log::info($data);
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            // Get ttd from tanda_tangan table
            $tddPath = base_path('../public_html/' . $data->ttd);
            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));
            $tddBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tddPath));
            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'fakultas' => $data->fakultas ?? '-',
                'prodi' => $data->prodi_mhs ?? '-',
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_penandatangan' => $data->nama_ttd ?? $data->kepala_biro,
                'jabatan_penandatangan' => 'Kepala Biro',
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
            ];

            $pdf = Pdf::loadView('pdf.administrasi_keuangan', $pdfData)
                ->setPaper('a4', 'portrait');
            // Log::info('pdf');
            $fileName = 'surat_keterangan_administrasi_keuangan_' . $data->nim . '.pdf';

            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Administrasi Keuangan';

            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganAdministrasiKeuangan::class);
            }

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
}
