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
            $petandaTangan = $validate['petanda_tangan'] ?? 'tidak';

            $login = Auth::user()?->prodi?->alias ?? 'UMUM';
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

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeterangan::join('prodi', 'prodi.id', '=', 'surat_keterangan.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan.tanda_tangan_id')
                ->select(
                    'surat_keterangan.*',
                    'prodi.nama as prodi_name',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                    'tanda_tangan.nama as nama_staff',
                )
                ->where('surat_keterangan.id', $sk->id)
                ->first();

            if ($data) {
                $kunci_jabatan = 'staff_' . strtolower($data->alias_prodi);
                $settingJabatan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', $kunci_jabatan)->first();

                $nama_kepala = $data->nama_kepala;
                $tddBase64 = '';
                $stempelBase64 = '';

                if ($settingJabatan && $settingJabatan->tandaTangan) {
                    $nama_kepala = $settingJabatan->tandaTangan->nama;
                }

                if (in_array($petandaTangan, ['ya', 'stempel'])) {
                    $stempelPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempelPath)) {
                        $stempelBase64 = SuratService::getBase64Image($stempelPath);
                    }
                }

                if (in_array($petandaTangan, ['ya'])) {
                    if ($settingJabatan && $settingJabatan->tandaTangan) {
                        $ttdSetting = $settingJabatan->tandaTangan->tdd ?? $settingJabatan->tandaTangan->gambar;
                        if (!empty($ttdSetting)) {
                            if (str_starts_with($ttdSetting, 'data:image')) {
                                $tddBase64 = $ttdSetting;
                            } else {
                                $tddPath = base_path('../public_html/' . $ttdSetting);
                                if (file_exists($tddPath)) {
                                    $tddBase64 = SuratService::getBase64Image($tddPath);
                                }
                            }
                        }
                    } else {
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
                }

                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);

                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama_mahasiswa' => $data->nama_mahasiswa,
                    'nim' => $data->nim,
                    'prodi' => $data->prodi,
                    'periode_bulan' => $data->periode_bulan,
                    'nama_staff' => $data->nama_staff,
                    'alasan' => $data->alasan,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'nama_kepala' => $nama_kepala,
                    'nidn_kepala' => $data->nidn_kepala,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                    'petanda_tangan' => $data->petanda_tangan ?? ($petandaTangan ?? 'tidak')
                ];

                $prodiFolder = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganController/');
                $pdf = Pdf::loadView('pdf.surat_keterangan', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratKeterangan::class);
            }

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
                'message' => 'Data gagal dihapus',
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
                'prodi_id' => 'required',
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi' => 'required|string|max:255',
                'periode_bulan' => 'required|string|max:255',
                'alasan' => 'required|string',
                'tanggal' => 'required|date',
                'pakai_tanda_tangan' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();
            $petandaTangan = $validate['petanda_tangan'] ?? 'tidak';

            $sk = SuratKeterangan::find($id);
            if (!$sk) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $sk->drive_file_id;
            $oldLocalPath = $sk->local_path;

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

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $sk->drive_file_id = null;
            $sk->drive_link = null;
            $sk->status = 'pending';
            $sk->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeterangan::join('prodi', 'prodi.id', '=', 'surat_keterangan.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan.tanda_tangan_id')
                ->select(
                    'surat_keterangan.*',
                    'prodi.nama as prodi_name',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                    'tanda_tangan.nama as nama_staff',
                )
                ->where('surat_keterangan.id', $sk->id)
                ->first();

            if ($data) {
                $kunci_jabatan = 'staff_' . strtolower($data->alias_prodi);
                $settingJabatan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', $kunci_jabatan)->first();

                $nama_kepala = $data->nama_kepala;
                $stempelBase64 = '';
                
                if (in_array($petandaTangan, ['ya', 'stempel'])) {
                    $stempelPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempelPath)) {
                        $stempelBase64 = SuratService::getBase64Image($stempelPath);
                    }
                }

                if (in_array($petandaTangan, ['ya'])) {
                    if ($settingJabatan && $settingJabatan->tandaTangan) {
                        $nama_kepala = $settingJabatan->tandaTangan->nama;
                        $ttdSetting = $settingJabatan->tandaTangan->tdd ?? $settingJabatan->tandaTangan->gambar;
                        if (!empty($ttdSetting)) {
                            if (str_starts_with($ttdSetting, 'data:image')) {
                                $tddBase64 = $ttdSetting;
                            } else {
                                $tddPath = base_path('../public_html/' . $ttdSetting);
                                if (file_exists($tddPath)) {
                                    $tddBase64 = SuratService::getBase64Image($tddPath);
                                }
                            }
                        }
                    } else {
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
                }

                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);

                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama_mahasiswa' => $data->nama_mahasiswa,
                    'nim' => $data->nim,
                    'prodi' => $data->prodi,
                    'periode_bulan' => $data->periode_bulan,
                    'nama_staff' => $data->nama_staff,
                    'alasan' => $data->alasan,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'nama_kepala' => $nama_kepala,
                    'nidn_kepala' => $data->nidn_kepala,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                    'petanda_tangan' => $data->petanda_tangan ?? ($petandaTangan ?? 'tidak')
                ];

                $prodiFolder = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganController/');
                $pdf = Pdf::loadView('pdf.surat_keterangan', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_' . $data->nim . '_' . uniqid() . '.pdf';
 
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
 
                $path = $directory . $fileName;
                $pdf->save($path);
 
                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratKeterangan::class);
            }

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

    public function downloadPdf($id)
    {
        try {
            $data = SuratKeterangan::find($id);

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
            return response()->json(['status' => true, 'data' => $prodi]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Gagal ambil data prodi']);
        }
    }
}
