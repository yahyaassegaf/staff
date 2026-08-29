<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganKkn;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganKknController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganKkn::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_kkn.prodi_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_kkn.tanda_tangan_id');

        $data->select(
            'surat_keterangan_kkn.id as id',
            'surat_keterangan_kkn.nomor_surat',
            'tanda_tangan.nama as ketua',
            'surat_keterangan_kkn.nama_lengkap',
            'surat_keterangan_kkn.tempat_lahir',
            'surat_keterangan_kkn.tanggal_lahir',
            'surat_keterangan_kkn.nim',
            'surat_keterangan_kkn.prodi_id',
            'surat_keterangan_kkn.jenis_kelamin',
            'surat_keterangan_kkn.prodi_mhs',
            'surat_keterangan_kkn.alamat_rumah',
            'surat_keterangan_kkn.kelas_pondok',
            'surat_keterangan_kkn.tanggal',
            'surat_keterangan_kkn.drive_file_id',
            'surat_keterangan_kkn.drive_link',
            'surat_keterangan_kkn.status',
            'surat_keterangan_kkn.created_at',
            'surat_keterangan_kkn.updated_at',
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
            $data->where('surat_keterangan_kkn.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_kkn.jenis_kelamin', 'P');
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
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal' => 'nullable|date',
                'petanda_tangan' => 'nullable|in:ya,tidak,stempel',
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
            Log::info($validate);

            $login = $validate['prodi_mhs'];
            $no_surat = $validate['no_surat'];

            // Reusing the service method as per previous controller patterns
            $noSurat = SuratService::NoSuratKeteranganTasmaKknPpl($no_surat);

            $skk                  = new SuratKeteranganKkn();
            $skk->nomor_surat     = $noSurat;
            $skk->ketua           = $validate['ketua'] ?? null;
            $skk->tanda_tangan_id = $validate['tanda_tangan_id'] ?? null;
            $skk->nama_lengkap    = $validate['nama_mhs'];
            $skk->tempat_lahir    = $validate['tempat_lahir'];
            $skk->tanggal_lahir   = $validate['tanggal_lahir'];
            $skk->nim             = $validate['nim'];
            $skk->prodi_id        = $validate['prodi_id'] ?? null;
            $skk->jenis_kelamin   = Auth::user()->jenis_kelamin;
            $skk->prodi_mhs       = $validate['prodi_mhs'];
            $skk->alamat_rumah    = $validate['alamat_rumah'];
            $skk->kelas_pondok    = $validate['kelas_pondok'];
            $skk->tanggal         = $validate['tanggal'];
            $skk->user_id         = Auth::user()->id;
            $skk->petanda_tangan  = $validate['petanda_tangan'] ?? 'tidak';
            $skk->status          = 'pending';
            $skk->save();

            $Nomor                = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id       = Auth::user()->id;
            $Nomor->save();

            $log                  = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan KKN';
            $log->user_id       = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganKkn::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_kkn.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'fakultas.nama as fakultas',
                    'surat_keterangan_kkn.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                    'tanda_tangan.nama as ttd_nama'
                )
                ->where('surat_keterangan_kkn.id', $skk->id)
                ->first();

            if ($data) {
                $tddBase64 = '';
                $stempBase64 = '';

                if (in_array($data->petanda_tangan, ['ya', 'stempel'])) {
                    $stempPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempPath)) {
                        $stempBase64 = SuratService::getBase64Image($stempPath);
                    }
                }
                
                if (in_array($data->petanda_tangan, ['ya'])) {
                    if (!empty($data->ttd)) {
                        if (str_starts_with($data->ttd, 'data:image')) {
                            $tddBase64 = $data->ttd;
                        } else {
                            $tddPath = base_path('../public_html/' . $data->ttd);
                            if (file_exists($tddPath)) {
                                $tddBase64 = SuratService::getBase64Image($tddPath);
                            }
                        }
                    }
                }

                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);

                $pdfData = [
                    'nomor_surat' => $data->nomor_surat,
                    'ketua' => $data->ttd_nama,
                    'nama' => $data->nama_lengkap,
                    'tempat_lahir' => $data->tempat_lahir,
                    'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                    'nim' => $data->nim,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'fakultas' => $data->fakultas,
                    'prodi' => $data->prodi_mhs,
                    'alamat' => $data->alamat_rumah,
                    'kelas' => $data->kelas_pondok,
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempBase64,
                ];

                $pdf = Pdf::loadView('pdf.kkn', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganKknController/');
                $fileName = 'surat_keterangan_kkn_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan KKN';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganKkn::class);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }

    public function show($id)
    {
        $skk = SuratKeteranganKkn::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_kkn.prodi_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_kkn.tanda_tangan_id')
            ->select(
                'surat_keterangan_kkn.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi',
            )
            ->where('surat_keterangan_kkn.id', $id)
            ->first();

        if (!$skk) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }


        $nomorStr = $skk->nomor_surat ?? $skk->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $skk->no_surat = trim($firstPart);
        }

        return response()->json([
            'status' => true,
            'data' => $skk,
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
                        $skk = \App\Models\SuratKeteranganKkn::find($id);
                        if ($skk) {
                            $originalNoSurat = '';
                            $nomorStr = $skk->nomor_surat ?? $skk->nomor ?? null;
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
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'petanda_tangan' => 'nullable|in:ya,tidak,stempel',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $skk = SuratKeteranganKkn::find($id);
            if (!$skk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $skk->drive_file_id;
            $oldLocalPath = $skk->local_path;

            $noSurat = \App\Services\SuratService::NoSuratKeteranganTasmaKknPpl($validate['no_surat']);
            $skk->nomor_surat = $noSurat;

            $skk->prodi_id = $validate['prodi_id'] ?? $skk->prodi_id;
            $skk->ketua = $validate['ketua'] ?? null;
            $skk->tanda_tangan_id = $validate['tanda_tangan_id'] ?? null;
            $skk->nama_lengkap = $validate['nama_mhs'];
            $skk->tempat_lahir = $validate['tempat_lahir'];
            $skk->tanggal_lahir = $validate['tanggal_lahir'];
            $skk->nim = $validate['nim'];
            $skk->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skk->prodi_mhs = $validate['prodi_mhs'];
            $skk->alamat_rumah = $validate['alamat_rumah'];
            $skk->kelas_pondok = $validate['kelas_pondok'];
            $skk->tanggal = $validate['tanggal'];
            $skk->prodi_id = $validate['prodi_id'];
            $skk->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';
            $skk->save();

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $skk->update([
                'drive_file_id' => null,
                'drive_link' => null,
                'status' => 'pending'
            ]);

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganKkn::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_kkn.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'fakultas.nama as fakultas',
                    'surat_keterangan_kkn.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                    'tanda_tangan.nama as ttd_nama'
                )
                ->where('surat_keterangan_kkn.id', $skk->id)
                ->first();

            if ($data) {
                $tddBase64 = '';
                $stempBase64 = '';

                if (in_array($data->petanda_tangan, ['ya', 'stempel'])) {
                    $stempPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempPath)) {
                        $stempBase64 = SuratService::getBase64Image($stempPath);
                    }
                }
                
                if (in_array($data->petanda_tangan, ['ya'])) {
                    if (!empty($data->ttd)) {
                        if (str_starts_with($data->ttd, 'data:image')) {
                            $tddBase64 = $data->ttd;
                        } else {
                            $tddPath = base_path('../public_html/' . $data->ttd);
                            if (file_exists($tddPath)) {
                                $tddBase64 = SuratService::getBase64Image($tddPath);
                            }
                        }
                    }
                }

                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);

                $pdfData = [
                    'nomor_surat' => $data->nomor_surat,
                    'ketua' => $data->ttd_nama,
                    'nama' => $data->nama_lengkap,
                    'tempat_lahir' => $data->tempat_lahir,
                    'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                    'nim' => $data->nim,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'fakultas' => $data->fakultas,
                    'prodi' => $data->prodi_mhs,
                    'alamat' => $data->alamat_rumah,
                    'kelas' => $data->kelas_pondok,
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempBase64,
                ];

                $pdf = Pdf::loadView('pdf.kkn', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganKknController/');
                $fileName = 'surat_keterangan_kkn_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan KKN';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganKkn::class);
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
            $skk = SuratKeteranganKkn::find($id);
            if (!$skk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $skk->delete();

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
            $data = SuratKeteranganKkn::find($id);

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
