<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganUjianKomprehensifDiniyah;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganUjianKomprehensifDiniyahController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganUjianKomprehensifDiniyah::join('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id');

        $data->select(
            'surat_keterangan_ujian_komprehensif_diniyah.*',
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

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_ujian_komprehensif_diniyah.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_ujian_komprehensif_diniyah.jenis_kelamin', 'P');
        }

        if ($request->filled('prodi_id')) {
            $data->where('prodi_id', $request->prodi_id);
        }

        $login  = Auth::user()->prodi;
        if ($login) {
            $data->where('prodi_id', $login->id);
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
                'prodi_id'      => 'required',
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
                'ttd'           => 'nullable|string',
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

            $noSurat = SuratService::formatNomorSurat('SKUKD', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $data = new SuratKeteranganUjianKomprehensifDiniyah();
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
            $settingKompre = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
            $data->tanda_tangan_id = $validate['tanda_tangan_id'] ?? ($settingKompre ? $settingKompre->tanda_tangan_id : null);

            // Set koor_komprehensif from tanda_tangan nama if provided, otherwise fallback
            if (!empty($validate['tanda_tangan_id'])) {
                $tandaTangan = \App\Models\TandaTangan::find($validate['tanda_tangan_id']);
                $data->koor_komprehensif = $tandaTangan?->nama;
                $data->ttd = $tandaTangan?->ttd;
            } else {
                $data->ttd = $validate['ttd'] ?? ($settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->gambar : null);
                $data->koor_komprehensif = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->nama : null;
            }
            $data->user_id = Auth::user()->id;
            $data->jenis_kelamin = Auth::user()->jenis_kelamin;
            $data->status = 'pending';
            $data->petanda_tangan = 'tidak';
            $data->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat = 'Surat Keterangan Ujian Komprehensif Diniyah';
            $log->user_id = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $refetched = SuratKeteranganUjianKomprehensifDiniyah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.tanda_tangan_id')
                ->select(
                    'surat_keterangan_ujian_komprehensif_diniyah.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'fakultas.nama as fakultas',
                    'tanda_tangan.nama as nama_ttd',
                    'tanda_tangan.gambar as ttd'
                )
                ->where('surat_keterangan_ujian_komprehensif_diniyah.id', $data->id)
                ->first();

            if ($refetched) {
                $settingKompre = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
                $namaKetua = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->nama : ($refetched->nama_ttd ?? $refetched->koor_komprehensif);
                $ttdKetua = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->gambar : $refetched->ttd;

                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);
                
                $tddBase64 = '';
                if (!empty($ttdKetua)) {
                    $tddPath = base_path('../public_html/' . $ttdKetua);
                    if (file_exists($tddPath)) {
                        $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
                    }
                }
                
                $stempelPath = base_path('../public_html/img/stempel.png');
                $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');

                $jabatan = 'Ketua / Koordinator Komprehensip';
                $pdfData = [
                    'nomor_surat' => $refetched->nomor_surat,
                    'nama' => $refetched->nama_lengkap,
                    'tempat_lahir' => $refetched->tempat_lahir,
                    'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($refetched->tanggal_lahir),
                    'nim' => $refetched->nim,
                    'fakultas' => $refetched->fakultas,
                    'prodi' => $refetched->nama_prodi,
                    'alamat' => $refetched->alamat_rumah,
                    'kelas' => $refetched->kelas_pondok,
                    'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($refetched->tanggal),
                    'nama_penandatangan' => $namaKetua,
                    'jabatan_penandatangan' => $jabatan,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64
                ];

                $pdf = Pdf::loadView('pdf.komprehensif', $pdfData)->setPaper('a4', 'portrait');
                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($refetched->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganUjianKomprehensifDiniyahController/');
                $fileName = 'surat_keterangan_ujian_komprehensif_diniyah_' . $refetched->nim . '_' . $refetched->alias_prodi . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $refetched->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Ujian Komprehensif Diniyah';
                UploudSuratToDrive::dispatch($refetched->id, $nameTable, $refetched->nama_prodi, SuratKeteranganUjianKomprehensifDiniyah::class);
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
        $data = SuratKeteranganUjianKomprehensifDiniyah::join('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id')
            ->select(
                'surat_keterangan_ujian_komprehensif_diniyah.*',
                'prodi.nama as nama_prodi'
            )
            ->where('surat_keterangan_ujian_komprehensif_diniyah.id', $id)
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
                        $data = \App\Models\SuratKeteranganUjianKomprehensifDiniyah::find($id);
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
                'prodi_id' => 'sometimes|exists:prodi,id',
                'nama_mhs' => 'sometimes|string|max:255',
                'tempat_lahir' => 'sometimes|string|max:100',
                'tanggal_lahir' => 'sometimes|date',
                'nim' => 'sometimes|string|max:255',
                'prodi_mhs' => 'sometimes|string|max:255',
                'alamat_rumah' => 'sometimes|string',
                'kelas_pondok' => 'sometimes|string|max:255',
                'tanggal' => 'sometimes|date',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
                'ttd' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();




            $data = SuratKeteranganUjianKomprehensifDiniyah::find($id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $oldDriveFileId = $data->drive_file_id;
            $oldLocalPath = $data->local_path;

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKUKD', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            $data->nomor_surat = $noSurat;

            if (array_key_exists('prodi_id', $validate)) {
                $data->prodi_id = $validate['prodi_id'];
            }
            if (array_key_exists('nama_mhs', $validate)) {
                $data->nama_lengkap = $validate['nama_mhs'];
            }
            if (array_key_exists('tempat_lahir', $validate)) {
                $data->tempat_lahir = $validate['tempat_lahir'];
            }
            if (array_key_exists('tanggal_lahir', $validate)) {
                $data->tanggal_lahir = $validate['tanggal_lahir'];
            }
            if (array_key_exists('nim', $validate)) {
                $data->nim = $validate['nim'];
            }
            if (array_key_exists('prodi_mhs', $validate)) {
                $data->prodi_mhs = $validate['prodi_mhs'];
            }
            if (array_key_exists('alamat_rumah', $validate)) {
                $data->alamat_rumah = $validate['alamat_rumah'];
            }
            if (array_key_exists('kelas_pondok', $validate)) {
                $data->kelas_pondok = $validate['kelas_pondok'];
            }
            if (array_key_exists('tanggal', $validate)) {
                $data->tanggal = $validate['tanggal'];
            }
            if (array_key_exists('tanda_tangan_id', $validate)) {
                $data->tanda_tangan_id = $validate['tanda_tangan_id'];
                // Update koor_komprehensif and ttd from tanda_tangan
                if (!empty($validate['tanda_tangan_id'])) {
                    $tandaTangan = \App\Models\TandaTangan::find($validate['tanda_tangan_id']);
                    $data->koor_komprehensif = $tandaTangan?->nama;
                    $data->ttd = $tandaTangan?->ttd;
                }
            } else if (empty($data->tanda_tangan_id)) {
                $settingKompre = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
                if ($settingKompre) {
                    $data->tanda_tangan_id = $settingKompre->tanda_tangan_id;
                    $data->koor_komprehensif = $settingKompre->tandaTangan ? $settingKompre->tandaTangan->nama : null;
                    $data->ttd = $settingKompre->tandaTangan ? $settingKompre->tandaTangan->gambar : null;
                }
            }

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
            $data->petanda_tangan = 'tidak';
            $data->save();

            // Re-fetch record with joins to build the new PDF
            $refetched = SuratKeteranganUjianKomprehensifDiniyah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.tanda_tangan_id')
                ->select(
                    'surat_keterangan_ujian_komprehensif_diniyah.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'fakultas.nama as fakultas',
                    'tanda_tangan.nama as nama_ttd',
                    'tanda_tangan.gambar as ttd'
                )
                ->where('surat_keterangan_ujian_komprehensif_diniyah.id', $data->id)
                ->first();

            if ($refetched) {
                $settingKompre = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
                $namaKetua = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->nama : ($refetched->nama_ttd ?? $refetched->koor_komprehensif);
                $ttdKetua = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->gambar : $refetched->ttd;

                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);
                
                $tddPath = base_path('../public_html/' . $ttdKetua);
                $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
                
                $stempelPath = base_path('../public_html/img/stempel.png');
                $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');

                $jabatan = 'Ketua / Koordinator Komprehensip';
                $pdfData = [
                    'nomor_surat' => $refetched->nomor_surat,
                    'nama' => $refetched->nama_lengkap,
                    'tempat_lahir' => $refetched->tempat_lahir,
                    'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($refetched->tanggal_lahir),
                    'nim' => $refetched->nim,
                    'fakultas' => $refetched->fakultas,
                    'prodi' => $refetched->nama_prodi,
                    'alamat' => $refetched->alamat_rumah,
                    'kelas' => $refetched->kelas_pondok,
                    'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($refetched->tanggal),
                    'nama_penandatangan' => $namaKetua,
                    'jabatan_penandatangan' => $jabatan,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64
                ];

                $pdf = Pdf::loadView('pdf.komprehensif', $pdfData)->setPaper('a4', 'portrait');
                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($refetched->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganUjianKomprehensifDiniyahController/');
                $fileName = 'surat_keterangan_ujian_komprehensif_diniyah_' . $refetched->nim . '_' . $refetched->alias_prodi . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $refetched->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Ujian Komprehensif Diniyah';
                UploudSuratToDrive::dispatch($refetched->id, $nameTable, $refetched->nama_prodi, SuratKeteranganUjianKomprehensifDiniyah::class);
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
            $data = SuratKeteranganUjianKomprehensifDiniyah::find($id);
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
        try {
            $data = SuratKeteranganUjianKomprehensifDiniyah::find($id);

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (empty($data->local_path) || !file_exists($data->local_path)) {
                return response()->json(['status' => false, 'message' => 'File PDF tidak ditemukan di server'], 404);
            }

            $fileName = basename($data->local_path);

            return response()->file($data->local_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            \Illuminate\Support\Facades\Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }
}
