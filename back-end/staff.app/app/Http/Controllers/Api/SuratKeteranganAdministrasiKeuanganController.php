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
                'no_surat'      => 'required|string|max:255|unique:nomor,nomor',
                'nama_mhs'      => 'required|string|max:255',
                'tempat_lahir'  => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim'           => 'required|string|max:255',
                'prodi_mhs'     => 'required|string|max:255',
                'alamat_rumah'  => 'required|string',
                'kelas_pondok'  => 'required|string|max:255',
                'tanggal'       => 'required|date',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
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

            $no_surat = $validate['no_surat'];

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKAK', $no_surat, $validate['tanggal'], $validate['prodi_id'] ?? null);

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
            $settingKeuangan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'kepala_biro_keuangan')->first();
            $data->tanda_tangan_id = $validate['tanda_tangan_id'] ?? ($settingKeuangan ? $settingKeuangan->tanda_tangan_id : null);
            // Set kepala_biro from tanda_tangan nama if provided
            if (!empty($validate['tanda_tangan_id'])) {
                $tandaTangan = \App\Models\TandaTangan::find($validate['tanda_tangan_id']);
                $data->kepala_biro = $tandaTangan?->nama;
            } else {
                $data->kepala_biro = $settingKeuangan && $settingKeuangan->tandaTangan ? $settingKeuangan->tandaTangan->nama : null;
            }
            $data->jenis_kelamin = Auth::user()->jenis_kelamin;
            $data->user_id      = Auth::user()->id;
            $data->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id    = Auth::user()->id;
            $Nomor->save();

            // Re-fetch record with joins to build the new PDF
            $refetched = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
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
                ->where('surat_keterangan_administrasi_keuangan.id', $data->id)
                ->first();

            if ($refetched) {
                $pdfData = $this->buildPdfData($refetched);
                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($refetched->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganAdministrasiKeuanganController');
                $pdf = Pdf::loadView('pdf.administrasi_keuangan', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_administrasi_keuangan_' . $refetched->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $refetched->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Administrasi Keuangan';
                UploudSuratToDrive::dispatch($refetched->id, $nameTable, $refetched->nama_prodi, SuratKeteranganAdministrasiKeuangan::class);
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


        $nomorStr = $data->nomor_surat ?? $data->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $data->no_surat = trim($firstPart);
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
                'no_surat' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $data = \App\Models\SuratKeteranganAdministrasiKeuangan::find($id);
                        if ($data) {
                            $originalNoSurat = '';
                            $nomorStr = $data->nomor_surat ?? $data->nomor ?? null;
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
                ], 404);
            }

            $oldDriveFileId = $data->drive_file_id;
            $oldLocalPath = $data->local_path;

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKAK', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            $data->nomor_surat = $noSurat;
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
                    $tandaTangan = \App\Models\TandaTangan::find($validate['tanda_tangan_id']);
                    $data->kepala_biro = $tandaTangan?->nama;
                }
            } else if (empty($data->tanda_tangan_id)) {
                $settingKeuangan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'kepala_biro_keuangan')->first();
                if ($settingKeuangan) {
                    $data->tanda_tangan_id = $settingKeuangan->tanda_tangan_id;
                    $data->kepala_biro = $settingKeuangan->tandaTangan ? $settingKeuangan->tandaTangan->nama : null;
                }
            }

            $data->jenis_kelamin = Auth::user()->jenis_kelamin;
            $data->user_id      = Auth::user()->id;

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $data->drive_file_id = null;
            $data->drive_link = null;
            $data->status = 'pending';
            $data->save();

            // Re-fetch record with joins to build the new PDF
            $refetched = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
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
                ->where('surat_keterangan_administrasi_keuangan.id', $data->id)
                ->first();

            if ($refetched) {
                $pdfData = $this->buildPdfData($refetched);
                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($refetched->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganAdministrasiKeuanganController');
                $pdf = Pdf::loadView('pdf.administrasi_keuangan', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_administrasi_keuangan_' . $refetched->nim . '_' . uniqid() . '.pdf';
 
                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }
 
                $path = $directory . '/' . $fileName;
                $pdf->save($path);
 
                $refetched->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Administrasi Keuangan';
                UploudSuratToDrive::dispatch($refetched->id, $nameTable, $refetched->nama_prodi, SuratKeteranganAdministrasiKeuangan::class);
            }

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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

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

            $pdfData = $this->buildPdfData($data);

            $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganAdministrasiKeuanganController');
            $pdf = Pdf::loadView('pdf.administrasi_keuangan', $pdfData)->setPaper('a4', 'portrait');
 
            $fileName = 'surat_keterangan_administrasi_keuangan_' . $data->nim . '_' . uniqid() . '.pdf';
 
            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }
 
            $path = $directory . '/' . $fileName;
            $pdf->save($path);
 
            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Administrasi Keuangan';
            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (empty($data->drive_file_id)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganAdministrasiKeuangan::class);
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
        $settingKeuangan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'kepala_biro_keuangan')->first();
        $namaKetua = $settingKeuangan && $settingKeuangan->tandaTangan ? $settingKeuangan->tandaTangan->nama : ($data->nama_ttd ?? $data->kepala_biro);
        $ttdKetua = $settingKeuangan && $settingKeuangan->tandaTangan ? $settingKeuangan->tandaTangan->gambar : $data->ttd;
        $namaJabatan = $settingKeuangan ? $settingKeuangan->nama_jabatan : 'Kepala Biro Administrasi Keuangan';

        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = SuratService::getBase64Image($kopPath);

        $tddPath = base_path('../public_html/' . $ttdKetua);
        $tddBase64 = SuratService::getBase64Image($tddPath);

        $stempelPath = base_path('../public_html/img/stempel.png');
        $stempelBase64 = SuratService::getBase64Image($stempelPath);

        return [
            'nomor_surat' => $data->nomor_surat,
            'nama' => $data->nama_lengkap,
            'tempat_lahir' => $data->tempat_lahir,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
            'nim' => $data->nim,
            'fakultas' => $data->fakultas ?? '-',
            'prodi' => $data->prodi_mhs ?? '-',
            'alamat' => $data->alamat_rumah,
            'kelas' => $data->kelas_pondok,
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'nama_penandatangan' => $namaKetua,
            'jabatan_penandatangan' => $namaJabatan,
            'kopBase64' => $kopBase64,
            'ttd' => $tddBase64,
            'stempel' => $stempelBase64,
        ];
    }
}
