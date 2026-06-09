<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganDaftarS2;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganDaftarS2Controller extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganDaftarS2::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_daftar_s2.prodi_id');

        $data->select(
            'surat_keterangan_daftar_s2.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan_daftar_s2.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan_daftar_s2.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('prodi', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_daftar_s2.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_daftar_s2.jenis_kelamin', 'P');
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
                'prodi_id' => 'required|exists:prodi,id',
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'nama_lengkap' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'petanda_tangan' => 'nullable|in:ya,tidak',
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

            $noSurat = SuratService::formatNomorSurat('SKMS', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $s2 = new SuratKeteranganDaftarS2();
            $s2->nomor_surat = $noSurat;
            $s2->prodi_id = $validate['prodi_id'];
            $s2->nama_lengkap = $validate['nama_lengkap'];
            $s2->nim = $validate['nim'];
            $s2->prodi = $validate['prodi'];
            $s2->tanggal = $validate['tanggal'];
            $s2->user_id = Auth::user()->id;
            $s2->jenis_kelamin = Auth::user()->jenis_kelamin;
            $s2->status = 'pending';
            $s2->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';
            $s2->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan Daftar S2';
            $log->user_id       = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganDaftarS2::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_daftar_s2.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_daftar_s2.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd')
                )
                ->where('surat_keterangan_daftar_s2.id', $s2->id)
                ->first();

            if ($data) {
                $staff = 'staff_' . $data->alias_prodi;
                $key = strtolower($staff);
                $jabatan = SettingJabatan::with('tandaTangan')->where('kunci_jabatan', $key)->first();

                $pdfData = $this->buildPdfData($data, $jabatan);
                $prodiFolder = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganDaftarS2Controller');
                $pdf = Pdf::loadView('pdf.surat_keterangan_daftar_s2', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_daftar_s2_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Daftar S2';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganDaftarS2::class);
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
        $s2 = SuratKeteranganDaftarS2::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_daftar_s2.prodi_id')
            ->select(
                'surat_keterangan_daftar_s2.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_daftar_s2.id', $id)
            ->first();

        if (!$s2) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }


        $nomorStr = $s2->nomor_surat ?? $s2->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $s2->no_surat = trim($firstPart);
        }

        return response()->json([
            'status' => true,
            'data' => $s2,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required|exists:prodi,id',
                'no_surat' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $s2 = \App\Models\SuratKeteranganDaftarS2::find($id);
                        if ($s2) {
                            $originalNoSurat = '';
                            $nomorStr = $s2->nomor_surat ?? $s2->nomor ?? null;
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
                'nama_lengkap' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'petanda_tangan' => 'nullable|in:ya,tidak',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $s2 = SuratKeteranganDaftarS2::find($id);
            if (!$s2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $s2->drive_file_id;
            $oldLocalPath = $s2->local_path;

            $noSurat = SuratService::formatNomorSurat('SKMS', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id']);

            $s2->fill([
                'nomor_surat' => $noSurat,
                'prodi_id' => $validate['prodi_id'],
                'nama_lengkap' => $validate['nama_lengkap'],
                'nim' => $validate['nim'],
                'prodi' => $validate['prodi'],
                'tanggal' => $validate['tanggal'],
                'jenis_kelamin' => Auth::user()->jenis_kelamin,
                'user_id' => Auth::user()->id,
                'petanda_tangan' => $validate['petanda_tangan'] ?? 'tidak',
            ]);

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $s2->drive_file_id = null;
            $s2->drive_link = null;
            $s2->status = 'pending';
            $s2->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganDaftarS2::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_daftar_s2.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_daftar_s2.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd')
                )
                ->where('surat_keterangan_daftar_s2.id', $s2->id)
                ->first();

            if ($data) {
                $staff = 'staff_' . $data->alias_prodi;
                $key = strtolower($staff);
                $jabatan = SettingJabatan::with('tandaTangan')->where('kunci_jabatan', $key)->first();

                $pdfData = $this->buildPdfData($data, $jabatan);
                $prodiFolder = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganDaftarS2Controller');
                $pdf = Pdf::loadView('pdf.surat_keterangan_daftar_s2', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_daftar_s2_' . $data->nim . '_' . uniqid() . '.pdf';
 
                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }
 
                $path = $directory . '/' . $fileName;
                $pdf->save($path);
 
                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Daftar S2';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganDaftarS2::class);
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
            $s2 = SuratKeteranganDaftarS2::find($id);
            if (!$s2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $s2->delete();

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
            $data = SuratKeteranganDaftarS2::find($id);

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
            Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }

    private function buildPdfData($data, $jabatan)
    {
        $stempelBase64 = '';
        $tddBase64 = '';

        if (isset($data->petanda_tangan) && $data->petanda_tangan === 'ya') {
            $stempelPath = base_path('../public_html/img/stempel.png');
            if (file_exists($stempelPath)) {
                $stempelBase64 = SuratService::getBase64Image($stempelPath);
            }

            $ttdImage = ($jabatan && $jabatan->tandaTangan) ? ($jabatan->tandaTangan->tdd ?? $jabatan->tandaTangan->gambar) : $data->ttd;
            if (!empty($ttdImage)) {
                if (str_starts_with($ttdImage, 'data:image')) {
                    $tddBase64 = $ttdImage;
                } else {
                    $tddPath = base_path('../public_html/' . $ttdImage);
                    if (file_exists($tddPath)) {
                        $tddBase64 = SuratService::getBase64Image($tddPath);
                    }
                }
            }
        }

        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = SuratService::getBase64Image($kopPath);

        return [
            'nomor_surat' => $data->nomor_surat,
            'nama' => $data->nama_lengkap,
            'nim' => $data->nim,
            'prodi' => $data->prodi,
            'tanggal' => $data->tanggal,
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'nama_prodi' => $data->nama_prodi,
            'alias_prodi' => $data->alias_prodi,
            'nama_kepala_prodi' => ($jabatan && $jabatan->tandaTangan) ? $jabatan->tandaTangan->nama : $data->nama_kepala_prodi,
            'nidn_kepala_prodi' => $jabatan ? $jabatan->nidn : $data->nidn_kepala_prodi,
            'kopBase64' => $kopBase64,
            'stempel' => $stempelBase64,
            'ttd' => $tddBase64
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
