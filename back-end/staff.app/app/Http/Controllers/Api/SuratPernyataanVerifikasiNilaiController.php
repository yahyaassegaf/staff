<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratPernyataanVerifikasiNilai;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratPernyataanVerifikasiNilaiController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id');

        $data->select(
            'surat_pernyataan_verifikasi_nilai.*',
            'prodi.nama as nama_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_pernyataan_verifikasi_nilai.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_pernyataan_verifikasi_nilai.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_mahasiswa', 'like', "%{$search}%")
                    ->orWhere('surat_pernyataan_verifikasi_nilai.prodi', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_pernyataan_verifikasi_nilai.jenis_kelamin', 'L');
        } else {
            $data->where('surat_pernyataan_verifikasi_nilai.jenis_kelamin', 'P');
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
                'nama_mhs' => 'required|string|max:255',
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

            $prodi = Prodi::find($validate['prodi_id']);
            if (!$prodi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Program Studi tidak ditemukan'
                ], 422);
            }

            $fakultas = \DB::table('fakultas')
                ->join('fakultas_prodi', 'fakultas_prodi.fakultas_id', '=', 'fakultas.id')
                ->join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id')
                ->where('prodi.alias', $prodi->alias)
                ->value('fakultas.nama');

            $login = Auth::user()?->prodi ? Auth::user()->prodi->alias : 'UMUM';
            $no_surat = $validate['no_surat'];

            $noSurat = SuratService::formatNomorSurat('SPMVN', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $surat = new SuratPernyataanVerifikasiNilai();
            $surat->nomor = $noSurat;
            $surat->tanda_tangan_id = $prodi->tanda_tangan_id;
            $surat->niy = $prodi->nidn_kepala ?? '';
            $surat->jabatan = 'Ketua Program Studi ' . $prodi->nama;
            $surat->nama_mahasiswa = $validate['nama_mhs'];
            $surat->nim = $validate['nim'];
            $surat->prodi_mhs = $validate['prodi'];
            $surat->fakultas = $fakultas ?? '';
            $surat->tanggal = $validate['tanggal'];
            $surat->prodi_id = $validate['prodi_id'];
            $surat->jenis_kelamin = Auth::user()->jenis_kelamin;
            $surat->user_id = Auth::user()->id;
            $surat->status = 'pending';
            $surat->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';
            $surat->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Pernyataan Verifikasi Nilai';
            $log->user_id       = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id')
                ->leftJoin('users', 'users.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_pernyataan_verifikasi_nilai.tanda_tangan_id')
                ->select(
                    'surat_pernyataan_verifikasi_nilai.*',
                    'users.name as nama_staff',
                    'prodi.nama as nama_prodi',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                    'tanda_tangan.nama as nama_penandatangan',
                    'fakultas.nama as nama_fakultas'
                )
                ->where('surat_pernyataan_verifikasi_nilai.id', $surat->id)
                ->first();

            if ($data) {
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

                $tddBase64 = '';
                $stempelBase64 = '';

                if (isset($data->petanda_tangan) && $data->petanda_tangan === 'ya') {
                    if (!empty($data->ttd)) {
                        if (str_starts_with($data->ttd, 'data:image')) {
                            $tddBase64 = $data->ttd;
                        } else {
                            $tddPath = base_path('../public_html/' . $data->ttd);
                            if (file_exists($tddPath)) {
                                $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
                            }
                        }
                    }

                    $stempelPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempelPath)) {
                        $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');
                    }
                }

                $nama = strtolower($data->nama_mahasiswa);
                $nim = strtolower($data->nim);

                $jabatan = 'Staff Program Studi ' . ucwords($data->nama_prodi) . ' Fakultas ' . ucwords($data->nama_fakultas);
                $staff = 'Staff Prodi ' . ucwords($data->nama_prodi);
                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama_penandatangan' => $data->nama_penandatangan,
                    'niy' => $data->niy,
                    'jabatan' => $jabatan,
                    'staff' => $staff,
                    'nama_mahasiswa' => $nama,
                    'nim' => $nim,
                    'prodi' => $data->nama_prodi,
                    'fakultas' => $data->nama_fakultas,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                ];

                $pdf = Pdf::loadView('pdf.surat_pernyataan_verifikasi_nilai', $pdfData)
                    ->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratPernyataanVerifikasiNilaiController/');
                $fileName = 'surat_pernyataan_verifikasi_nilai_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Pernyataan Verifikasi Nilai';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratPernyataanVerifikasiNilai::class);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan: ' . $th->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_pernyataan_verifikasi_nilai.tanda_tangan_id')
            ->select(
                'surat_pernyataan_verifikasi_nilai.*',
                'prodi.nama as nama_prodi',
                'tanda_tangan.nama as nama_ttd'
            )
            ->where('surat_pernyataan_verifikasi_nilai.id', $id)
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
        try {

            $validator = Validator::make($request->all(), [
                'no_surat' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $surat = \App\Models\SuratPernyataanVerifikasiNilai::find($id);
                        if ($surat) {
                            $originalNoSurat = '';
                            $nomorStr = $surat->nomor_surat ?? $surat->nomor ?? null;
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
                'prodi_id' => 'required|exists:prodi,id',
                'nama_mhs' => 'required|string|max:255',
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

            $surat = SuratPernyataanVerifikasiNilai::find($id);
            if (!$surat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $oldDriveFileId = $surat->drive_file_id;
            $oldLocalPath = $surat->local_path;

            $prodi = Prodi::find($validate['prodi_id']);
            if (!$prodi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Program Studi tidak ditemukan'
                ], 422);
            }

            $noSurat = \App\Services\SuratService::formatNomorSurat('SPMVN', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            $surat->nomor = $noSurat;

            $fakultas = \DB::table('fakultas')
                ->join('fakultas_prodi', 'fakultas_prodi.fakultas_id', '=', 'fakultas.id')
                ->join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id')
                ->where('prodi.alias', $prodi->alias)
                ->value('fakultas.nama');

            $surat->tanda_tangan_id = $prodi->tanda_tangan_id;
            $surat->niy = $prodi->nidn_kepala ?? '';
            $surat->jabatan = 'Ketua Program Studi ' . $prodi->nama;
            $surat->nama_mahasiswa = $validate['nama_mhs'];
            $surat->nim = $validate['nim'];
            $surat->prodi_mhs = $validate['prodi'];
            $surat->fakultas = $fakultas ?? '';
            $surat->tanggal = $validate['tanggal'];
            $surat->prodi_id = $validate['prodi_id'];
            $surat->jenis_kelamin = Auth::user()->jenis_kelamin;
            $surat->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $surat->drive_file_id = null;
            $surat->drive_link = null;
            $surat->status = 'pending';
            $surat->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratPernyataanVerifikasiNilai::leftJoin('prodi', 'prodi.id', '=', 'surat_pernyataan_verifikasi_nilai.prodi_id')
                ->leftJoin('users', 'users.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_pernyataan_verifikasi_nilai.tanda_tangan_id')
                ->select(
                    'surat_pernyataan_verifikasi_nilai.*',
                    'users.name as nama_staff',
                    'prodi.nama as nama_prodi',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                    'tanda_tangan.nama as nama_penandatangan',
                    'fakultas.nama as nama_fakultas'
                )
                ->where('surat_pernyataan_verifikasi_nilai.id', $surat->id)
                ->first();

            if ($data) {
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

                $nama = strtolower($data->nama_mahasiswa);
                $nim = strtolower($data->nim);

                $tddBase64 = '';
                $stempelBase64 = '';

                if (isset($data->petanda_tangan) && $data->petanda_tangan === 'ya') {
                    if (!empty($data->ttd)) {
                        $tddPath = base_path('../public_html/' . $data->ttd);
                        if (file_exists($tddPath)) {
                            $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
                        }
                    }

                    $stempelPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempelPath)) {
                        $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');
                    }
                }

                $jabatan = 'Staff Program Studi ' . ucwords($data->nama_prodi) . ' Fakultas ' . ucwords($data->nama_fakultas);
                $staff = 'Staff Prodi ' . ucwords($data->nama_prodi);
                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama_penandatangan' => $data->nama_penandatangan,
                    'niy' => $data->niy,
                    'jabatan' => $jabatan,
                    'staff' => $staff,
                    'nama_mahasiswa' => $nama,
                    'nim' => $nim,
                    'prodi' => $data->nama_prodi,
                    'fakultas' => $data->nama_fakultas,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                ];

                $pdf = Pdf::loadView('pdf.surat_pernyataan_verifikasi_nilai', $pdfData)
                    ->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratPernyataanVerifikasiNilaiController/');
                $fileName = 'surat_pernyataan_verifikasi_nilai_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Pernyataan Verifikasi Nilai';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratPernyataanVerifikasiNilai::class);
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
            $surat = SuratPernyataanVerifikasiNilai::find($id);
            if (!$surat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $surat->delete();

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
            $data = SuratPernyataanVerifikasiNilai::find($id);

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

    public function getProdi()
    {
        try {
            $login = Auth::user()->prodi;
            if ($login) {
                $prodi = Prodi::where('id', $login->id)->get();
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
