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
            'surat_keterangan_qismul_aman.drive_link',
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
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'ketua' => 'nullable|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jenis_kelamin' => 'nullable|string|max:50',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal_berlaku_dari' => 'required|date',
                'tanggal_berlaku_sampai' => 'required|date',
                'tanggal' => 'required|date',
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

            $login = Auth::user()?->prodi ? Auth::user()->prodi->alias : 'UMUM';
            $no_surat = $validate['no_surat'];

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKQA', $no_surat, $validate['tanggal'], $validate['prodi_id'] ?? null);

            $skqa = new SuratKeteranganQismulAman();
            $skqa->nomor_surat = $noSurat;
            $settingJabatan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_qismul_aman')->first();
            $skqa->ketua = $settingJabatan && $settingJabatan->tandaTangan ? $settingJabatan->tandaTangan->nama : null;
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
            $Nomor->nomor = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan Qismul Aman';
            $log->user_id       = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganQismulAman::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_qismul_aman.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_qismul_aman.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_qismul_aman.id', $skqa->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.surat_qismul_aman', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganQismulAmanController');
                $fileName = 'surat_keterangan_qismul_aman_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Qismul Aman';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganQismulAman::class);
            }

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

        
        $nomorStr = $skqa->nomor_surat ?? $skqa->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $skqa->no_surat = trim($firstPart);
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
                'no_surat' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $skqa = \App\Models\SuratKeteranganQismulAman::find($id);
                        if ($skqa) {
                            $originalNoSurat = '';
                            $nomorStr = $skqa->nomor_surat ?? $skqa->nomor ?? null;
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
                'prodi_id' => 'nullable|exists:prodi,id',
                'ketua' => 'nullable|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jenis_kelamin' => 'nullable|string|max:50',
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

            $oldDriveFileId = $skqa->drive_file_id;
            $oldLocalPath = $skqa->local_path;

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKQA', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            $skqa->nomor_surat = $noSurat;

            $skqa->prodi_id = $validate['prodi_id'] ?? $skqa->prodi_id;
            $settingJabatan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_qismul_aman')->first();
            $skqa->ketua = $settingJabatan && $settingJabatan->tandaTangan ? $settingJabatan->tandaTangan->nama : null;
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

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $skqa->drive_file_id = null;
            $skqa->drive_link = null;
            $skqa->status = 'pending';
            $skqa->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganQismulAman::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_qismul_aman.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_qismul_aman.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_qismul_aman.id', $skqa->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.surat_qismul_aman', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganQismulAmanController');
                $fileName = 'surat_keterangan_qismul_aman_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Qismul Aman';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganQismulAman::class);
            }

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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

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

            $pdfData = $this->buildPdfData($data);

            $pdf = Pdf::loadView('pdf.surat_qismul_aman', $pdfData)->setPaper('a4', 'portrait');

            $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganQismulAmanController');
            $fileName = 'surat_keterangan_qismul_aman_' . $data->nim . '_' . uniqid() . '.pdf';

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Qismul Aman';
            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (empty($data->drive_file_id)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganQismulAman::class);
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
        $settingJabatan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_qismul_aman')->first();
        $tddBase64 = '';
        if ($settingJabatan && $settingJabatan->tandaTangan && $settingJabatan->tandaTangan->gambar) {
            $tddPath = base_path('../public_html/' . $settingJabatan->tandaTangan->gambar);
            if (file_exists($tddPath)) {
                $tddBase64 = SuratService::getBase64Image($tddPath);
            }
        }

        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = SuratService::getBase64Image($kopPath);

        return [
            'nomor_surat' => $data->nomor_surat,
            'ketua' => $data->ketua ?? ($settingJabatan && $settingJabatan->tandaTangan ? $settingJabatan->tandaTangan->nama : null),
            'nama' => $data->nama_lengkap,
            'tempat_lahir' => $data->tempat_lahir,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
            'nim' => $data->nim,
            'prodi' => $data->prodi_mhs,
            'alamat' => $data->alamat_rumah,
            'kelas' => $data->kelas_pondok,
            'jenis_kelamin' => $data->jenis_kelamin,
            'tanggal_berlaku_dari' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_berlaku_dari),
            'tanggal_berlaku_sampai' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_berlaku_sampai),
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'kopBase64' => $kopBase64,
            'ttd' => $tddBase64,
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
