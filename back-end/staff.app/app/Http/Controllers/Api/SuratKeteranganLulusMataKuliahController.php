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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            'surat_keterangan_lulus_mata_kuliah.drive_link',
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

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_lulus_mata_kuliah.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_lulus_mata_kuliah.jenis_kelamin', 'P');
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
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal' => 'nullable|date',
            ], [
                'no_surat.unique' => 'Nomor surat sudah terpakai',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $login = Auth::user()?->prodi?->alias ?? 'UMUM';
            $no_surat = $validate['no_surat'];

            $tanggal = $validate['tanggal'] ?? date('Y-m-d');
            $noSurat = \App\Services\SuratService::formatNomorSurat('SKLM', $no_surat, $tanggal, $validate['prodi_id']);

            $sklmk                  = new SuratKeteranganLulusMataKuliah();
            $sklmk->nomor_surat     = $noSurat;
            $sklmk->prodi_id        = $validate['prodi_id'];
            $sklmk->nama_lengkap    = $validate['nama_mhs'];
            $sklmk->tempat_lahir    = $validate['tempat_lahir'];
            $sklmk->tanggal_lahir   = $validate['tanggal_lahir'];
            $sklmk->nim             = $validate['nim'];
            $sklmk->prodi_mahasiswa = $validate['prodi_mhs'];
            $sklmk->alamat_rumah    = $validate['alamat_rumah'];
            $sklmk->kelas_pondok    = $validate['kelas_pondok'];
            $sklmk->tanggal         = $validate['tanggal'] ?? date('Y-m-d');
            $sklmk->user_id         = Auth::user()->id;
            $sklmk->jenis_kelamin   = Auth::user()->jenis_kelamin;
            $sklmk->status          = $validate['status'] ?? 'pending';
            $sklmk->save();

            $Nomor                  = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id         = Auth::user()->id;
            $Nomor->save();

            $log                    = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat = 'Surat Keterangan Lulus Mata Kuliah';
            $log->user_id = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganLulusMataKuliah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_keterangan_lulus_mata_kuliah.*',
                    'prodi.nama as nama_prodi',
                    'fakultas.nama as fakultas',
                    'fakultas.dekan as dekan',
                    'fakultas.nidn_dekan as nidn_dekan',
                    'prodi.alias as alias_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_lulus_mata_kuliah.id', $sklmk->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.v_surat_keterangan_lulus_mata_kuliah', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganLulusMataKuliahController');
                $fileName = 'surat_keterangan_lulus_mata_kuliah_' . $data->nim . '_' . $data->prodi_mahasiswa . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Lulus Mata Kuliah';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganLulusMataKuliah::class);
            }

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


        $nomorStr = $sklmk->nomor_surat ?? $sklmk->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $sklmk->no_surat = trim($firstPart);
        }

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
            $validator = Validator::make($request->all(), [
                'no_surat' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $sklmk = \App\Models\SuratKeteranganLulusMataKuliah::find($id);
                        if ($sklmk) {
                            $originalNoSurat = '';
                            $nomorStr = $sklmk->nomor_surat ?? $sklmk->nomor ?? null;
                            if ($nomorStr) {
                                $parts = explode('/', $nomorStr);
                                $firstPart = $parts[0];
                                if (strpos($firstPart, '-') !== false) {
                                    $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
                                }
                                $originalNoSurat = trim($firstPart);
                            }
                            if ($value !== $originalNoSurat) {
                                if (\App\Models\NoSurat::where('nomor', $value)->exists()) {
                                    $fail('Nomor surat sudah terpakai.');
                                }
                            }
                        }
                    }
                ],
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

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $sklmk = SuratKeteranganLulusMataKuliah::find($id);
            if (!$sklmk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $oldDriveFileId = $sklmk->drive_file_id;
            $oldLocalPath = $sklmk->local_path;

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKLM', $validate['no_surat'], $validate['tanggal'] ?? date('Y-m-d'), $validate['prodi_id'] ?? null);
            $sklmk->nomor_surat = $noSurat;

            $sklmk->prodi_id = $validate['prodi_id'] ?? $sklmk->prodi_id;
            $sklmk->nama_lengkap = $validate['nama_mhs'];
            $sklmk->tempat_lahir = $validate['tempat_lahir'];
            $sklmk->tanggal_lahir = $validate['tanggal_lahir'];
            $sklmk->nim = $validate['nim'];
            $sklmk->prodi_mahasiswa = $validate['prodi_mhs'];
            $sklmk->alamat_rumah = $validate['alamat_rumah'];
            $sklmk->kelas_pondok = $validate['kelas_pondok'];
            $sklmk->tanggal = $validate['tanggal'];
            $sklmk->jenis_kelamin = Auth::user()->jenis_kelamin;
            
            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $sklmk->drive_file_id = null;
            $sklmk->drive_link = null;
            $sklmk->status = 'pending';
            $sklmk->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganLulusMataKuliah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_keterangan_lulus_mata_kuliah.*',
                    'prodi.nama as nama_prodi',
                    'fakultas.nama as fakultas',
                    'fakultas.dekan as dekan',
                    'fakultas.nidn_dekan as nidn_dekan',
                    'prodi.alias as alias_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_lulus_mata_kuliah.id', $sklmk->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.v_surat_keterangan_lulus_mata_kuliah', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganLulusMataKuliahController');
                $fileName = 'surat_keterangan_lulus_mata_kuliah_' . $data->nim . '_' . $data->prodi_mahasiswa . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Lulus Mata Kuliah';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganLulusMataKuliah::class);
            }

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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        try {
            $data = SuratKeteranganLulusMataKuliah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_keterangan_lulus_mata_kuliah.*',
                    'prodi.nama as nama_prodi',
                    'fakultas.nama as fakultas',
                    'fakultas.dekan as dekan',
                    'fakultas.nidn_dekan as nidn_dekan',
                    'prodi.alias as alias_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_lulus_mata_kuliah.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $pdfData = $this->buildPdfData($data);

            $pdf = Pdf::loadView('pdf.v_surat_keterangan_lulus_mata_kuliah', $pdfData)->setPaper('a4', 'portrait');

            $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganLulusMataKuliahController');
            $fileName = 'surat_keterangan_lulus_mata_kuliah_' . $data->nim . '_' . $data->prodi_mahasiswa . '_' . uniqid() . '.pdf';

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Lulus Mata Kuliah';
            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (empty($data->drive_file_id)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganLulusMataKuliah::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error((string) $th);
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengunduh PDF: Terjadi kesalahan pada server'
            ], 500);
        }
    }

    private function buildPdfData($data)
    {
        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = SuratService::getBase64Image($kopPath);

        $stempelPath = base_path('../public_html/img/stempel.png');
        $stempelBase64 = SuratService::getBase64Image($stempelPath);

        $tddPath = base_path('../public_html/' . $data->ttd);
        $tddBase64 = SuratService::getBase64Image($tddPath);

        return [
            'nomor_surat' => $data->nomor_surat,
            'nama' => $data->nama_lengkap,
            'tempat_lahir' => $data->tempat_lahir,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
            'nim' => $data->nim,
            'fakultas' => $data->fakultas,
            'prodi' => $data->nama_prodi,
            'alamat' => $data->alamat_rumah,
            'kelas' => $data->kelas_pondok,
            'alias_prodi' => $data->alias_prodi,
            'dekan' => $data->dekan,
            'nidn_dekan' => $data->nidn_dekan,
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'stempel' => $stempelBase64,
            'ttd' => $tddBase64,
            'kopBase64' => $kopBase64,
        ];
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
