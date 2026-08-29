<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\FakultasProdi;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratIzinPenelitian;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratIzinPenelitianController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratIzinPenelitian::join('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id');

        $data->select(
            'surat_izin_penelitian.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_izin_penelitian.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_izin_penelitian.prodi_id', $login->id);
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_izin_penelitian.jenis_kelamin', 'L');
        } else {
            $data->where('surat_izin_penelitian.jenis_kelamin', 'P');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_izin_penelitian.nim', 'like', "%{$search}%")
                    ->orWhere('surat_izin_penelitian.nama', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%");
            });
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
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'nama' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'nullable|string|max:255',
                'kepada' => 'nullable|string|max:255',
                'semester' => 'required|string|max:255',
                'dari_tanggal' => 'required|date',
                'tanggal' => 'required|date',
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

            $no_surat = $validate['no_surat'];

            $formattedNoSurat = SuratService::formatNomorSurat('SIP', $no_surat, $validate['tanggal'], $validate['prodi_id']);
            $sip = new SuratIzinPenelitian();
            $sip->nomor = $formattedNoSurat;
            $sip->nama = $validate['nama'];
            $sip->nim = $validate['nim'];
            $sip->kepada = $validate['kepada'];
            $sip->prodi_id = $validate['prodi_id'];
            $sip->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';
            $sip->semester = $validate['semester'];
            $sip->dari_tanggal = $validate['dari_tanggal'];
            $sip->tanggal = $validate['tanggal'];
            $sip->user_id = Auth::user()->id;
            $sip->jenis_kelamin = Auth::user()->jenis_kelamin;
            $sip->status = 'pending';
            $sip->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Izin Penelitian';
            $log->user_id = Auth::user()->id;
            $log->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratIzinPenelitian::leftJoin('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_izin_penelitian.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    'fakultas.dekan as nama_dekan',
                    'fakultas.nidn_dekan',
                    'tanda_tangan.nama as nama_ttd',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                )
                ->where('surat_izin_penelitian.id', $sip->id)
                ->first();

            if ($data) {
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);

                $tddBase64 = '';
                $stempelBase64 = '';

                if (in_array($data->petanda_tangan, ['ya', 'stempel'])) {
                    $stempelPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempelPath)) {
                        $stempelBase64 = SuratService::getBase64Image($stempelPath);
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

                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama' => $data->nama,
                    'nim' => $data->nim,
                    'kepada' => $data->kepada,
                    'semester' => $data->semester,
                    'dari_tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->dari_tanggal),
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'nama_dekan' => $data->nama_dekan ?? $data->nama_ttd,
                    'nidn_dekan' => $data->nidn_dekan,
                    'prodi_name' => $data->prodi_name,
                    'fakultas_name' => $data->fakultas_name,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                    'petanda_tangan' => $data->petanda_tangan ?? ($petandaTangan ?? 'tidak')
                ];

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratIzinPenelitianController/');
                $pdf = Pdf::loadView('pdf.surat_izin_penelitian', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_izin_penelitian_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Izin Penelitian';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratIzinPenelitian::class);
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

    public function show($id)
    {
        $data = SuratIzinPenelitian::join('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id')
            ->select('surat_izin_penelitian.*', 'prodi.nama as nama_prodi')
            ->where('surat_izin_penelitian.id', $id)
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
                        $sip = \App\Models\SuratIzinPenelitian::find($id);
                        if ($sip) {
                            $originalNoSurat = '';
                            $nomorStr = $sip->nomor_surat ?? $sip->nomor ?? null;
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
                'prodi_id' => 'required',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
                'nama' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'nullable|string|max:255',
                'kepada' => 'nullable|string|max:255',
                'semester' => 'required|string|max:255',
                'dari_tanggal' => 'required|date',
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

            $sip = SuratIzinPenelitian::find($id);
            if (!$sip) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $sip->drive_file_id;
            $oldLocalPath = $sip->local_path;

            $sip->nama = $validate['nama'];
            $sip->nim = $validate['nim'];
            $sip->prodi_mhs = $validate['prodi_mhs'] ?? $sip->prodi_mhs;
            $sip->kepada = $validate['kepada'] ?? $sip->kepada;
            $sip->semester = $validate['semester'];
            $sip->dari_tanggal = $validate['dari_tanggal'];
            $sip->tanggal = $validate['tanggal'];
            $sip->prodi_id = $validate['prodi_id'];
            if (array_key_exists('tanda_tangan_id', $validate)) {
                $sip->tanda_tangan_id = $validate['tanda_tangan_id'];
            }

            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('SIP', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            $sip->nomor = $formattedNoSurat;


            $sip->jenis_kelamin = Auth::user()->jenis_kelamin;
            $sip->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $sip->drive_file_id = null;
            $sip->drive_link = null;
            $sip->status = 'pending';
            $sip->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratIzinPenelitian::leftJoin('prodi', 'prodi.id', '=', 'surat_izin_penelitian.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_izin_penelitian.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    'fakultas.dekan as nama_dekan',
                    'fakultas.nidn_dekan',
                    'tanda_tangan.nama as nama_ttd',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd'),
                )
                ->where('surat_izin_penelitian.id', $sip->id)
                ->first();

            if ($data) {
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);

                $tddBase64 = '';
                $stempelBase64 = '';

                if (in_array($data->petanda_tangan, ['ya', 'stempel'])) {
                    $stempelPath = base_path('../public_html/img/stempel.png');
                    if (file_exists($stempelPath)) {
                        $stempelBase64 = SuratService::getBase64Image($stempelPath);
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

                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama' => $data->nama,
                    'nim' => $data->nim,
                    'kepada' => $data->kepada,
                    'semester' => $data->semester,
                    'dari_tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->dari_tanggal),
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'nama_dekan' => $data->nama_dekan ?? $data->nama_ttd,
                    'nidn_dekan' => $data->nidn_dekan,
                    'prodi_name' => $data->prodi_name,
                    'fakultas_name' => $data->fakultas_name,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                    'petanda_tangan' => $data->petanda_tangan ?? ($petandaTangan ?? 'tidak')
                ];

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratIzinPenelitianController/');
                $pdf = Pdf::loadView('pdf.surat_izin_penelitian', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_izin_penelitian_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Izin Penelitian';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratIzinPenelitian::class);
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

    public function destroy($id)
    {
        try {
            $sip = SuratIzinPenelitian::find($id);
            if (!$sip) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $sip->delete();
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
            $data = SuratIzinPenelitian::find($id);

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
