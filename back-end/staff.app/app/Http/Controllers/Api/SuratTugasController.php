<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratTugas;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratTugasController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratTugas::join('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id');

        $data->select(
            'surat_tugas.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_tugas.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_tugas.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_tugas.nim_nik', 'like', "%{$search}%")
                    ->orWhere('surat_tugas.nama_mhs', 'like', "%{$search}%")
                    ->orWhere('surat_tugas.nama_dosen', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_tugas.jenis_kelamin', 'L');
        } else {
            $data->where('surat_tugas.jenis_kelamin', 'P');
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
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'nama_dosen' => 'required|string|max:255',
                'alamat_dosen' => 'required|string|max:255',
                'tugas_dosen' => 'required|string|max:255',
                'tugasnya' => 'required|string',
                'nama_mhs' => 'required|string|max:255',
                'nim_nik' => 'required|string|max:255',
                'fakultas_prodi' => 'required|string|max:255',
                'judul_skripsi' => 'required|string',
                'masa_penugasan' => 'required|date',
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

            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('ST', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $st = new SuratTugas();
            $st->nomor = $formattedNoSurat;
            $st->nama_dosen = $validate['nama_dosen'];
            $st->alamat_dosen = $validate['alamat_dosen'];
            $st->tugas_dosen = $validate['tugas_dosen'];
            $st->tugasnya = $validate['tugasnya'];
            $st->nama_mhs = $validate['nama_mhs'];
            $st->nim_nik = $validate['nim_nik'];
            $st->fakultas_prodi = $validate['fakultas_prodi'];
            $st->judul_skripsi = $validate['judul_skripsi'];
            $st->masa_penugasan = $validate['masa_penugasan'];
            $st->tanggal = $validate['tanggal'];
            $st->user_id = Auth::user()->id;
            $st->prodi_id = $validate['prodi_id'];
            $st->jenis_kelamin = Auth::user()->jenis_kelamin;
            $st->status = 'pending';
            $st->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Tugas';
            $log->user_id = Auth::user()->id;
            $log->save();

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
            $st = SuratTugas::find($id);
            if (!$st) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $st->delete();

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
        $data = SuratTugas::join('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id')
            ->select('surat_tugas.*', 'prodi.nama as nama_prodi')
            ->where('surat_tugas.id', $id)
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
                        $st = \App\Models\SuratTugas::find($id);
                        if ($st) {
                            $originalNoSurat = '';
                            $nomorStr = $st->nomor_surat ?? $st->nomor ?? null;
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
                'nama_dosen' => 'required|string|max:255',
                'alamat_dosen' => 'required|string|max:255',
                'tugas_dosen' => 'required|string|max:255',
                'tugasnya' => 'required|string',
                'nama_mhs' => 'required|string|max:255',
                'nim_nik' => 'required|string|max:255',
                'fakultas_prodi' => 'required|string|max:255',
                'judul_skripsi' => 'required|string',
                'masa_penugasan' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jenis_kelamin' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $st = SuratTugas::find($id);
            if (!$st) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $st->drive_file_id;
            $oldLocalPath = $st->local_path;

            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('ST', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            $st->nomor = $formattedNoSurat;
            $st->nama_dosen = $validate['nama_dosen'];
            $st->alamat_dosen = $validate['alamat_dosen'];
            $st->tugas_dosen = $validate['tugas_dosen'];
            $st->tugasnya = $validate['tugasnya'];
            $st->nama_mhs = $validate['nama_mhs'];
            $st->nim_nik = $validate['nim_nik'];
            $st->fakultas_prodi = $validate['fakultas_prodi'];
            $st->judul_skripsi = $validate['judul_skripsi'];
            $st->masa_penugasan = $validate['masa_penugasan'];
            $st->tanggal = $validate['tanggal'];
            $st->prodi_id = $validate['prodi_id'];
            $st->jenis_kelamin = $validate['jenis_kelamin'];

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $st->drive_file_id = null;
            $st->drive_link = null;
            $st->status = 'pending';
            $st->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratTugas::leftJoin('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_tugas.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    'tanda_tangan.gambar as ttd'
                )
                ->where('surat_tugas.id', $st->id)
                ->first();

            if ($data) {
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);
                $tddPath = base_path('../public_html/' . $data->ttd);

                $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
                $stempelPath = base_path('../public_html/img/stempel.png');

                $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');
                $pdfData = [
                    'nomor' => $data->nomor,
                    'nama_dosen' => $data->nama_dosen,
                    'alamat_dosen' => $data->alamat_dosen,
                    'tugas_dosen' => $data->tugas_dosen,
                    'tugasnya' => $data->tugasnya,
                    'nama_mhs' => $data->nama_mhs,
                    'nim_nik' => $data->nim_nik,
                    'fakultas_prodi' => $data->fakultas_prodi,
                    'judul_skripsi' => $data->judul_skripsi,
                    'masa_penugasan' => $data->masa_penugasan,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'nama_kepala' => $data->nama_kepala,
                    'nidn_kepala' => $data->nidn_kepala,
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                ];

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratTugasController/');
                $pdf = Pdf::loadView('pdf.surat_tugas', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_tugas_' . $data->nim_nik . '_' . uniqid() . '.pdf';
 
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
 
                $path = $directory . $fileName;
                $pdf->save($path);
 
                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Tugas';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratTugas::class);
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

    public function downloadPdf($id)
    {
        try {
            $data = SuratTugas::leftJoin('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_tugas.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    'tanda_tangan.gambar as ttd'
                )
                ->where('surat_tugas.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);
            $tddPath = base_path('../public_html/' . $data->ttd);

            $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
            $stempelPath = base_path('../public_html/img/stempel.png');

            $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');
            $pdfData = [
                'nomor' => $data->nomor,
                'nama_dosen' => $data->nama_dosen,
                'alamat_dosen' => $data->alamat_dosen,
                'tugas_dosen' => $data->tugas_dosen,
                'tugasnya' => $data->tugasnya,
                'nama_mhs' => $data->nama_mhs,
                'nim_nik' => $data->nim_nik,
                'fakultas_prodi' => $data->fakultas_prodi,
                'judul_skripsi' => $data->judul_skripsi,
                'masa_penugasan' => $data->masa_penugasan,
                'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'nama_kepala' => $data->nama_kepala,
                'nidn_kepala' => $data->nidn_kepala,
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
            ];

            $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratTugasController/');
            $pdf = Pdf::loadView('pdf.surat_tugas', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_tugas_' . $data->nim_nik . '_' . uniqid() . '.pdf';
 
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
 
            $path = $directory . $fileName;
            $pdf->save($path);
 
            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Tugas';

            $googlePath = $data->prodi_name . '/' . $nameTable . '/' . $fileName;

            if (empty($data->drive_file_id)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratTugas::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal generate PDF']);
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
