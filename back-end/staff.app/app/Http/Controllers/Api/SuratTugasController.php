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
                    ->orWhere('surat_tugas.pembimbing1', 'like', "%{$search}%")
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
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'pembimbing1' => 'nullable|string|max:255',
                'alamat_pembimbing1' => 'nullable|string|max:255',
                'tugas_pembimbing1' => 'nullable|string|max:255',
                'pembimbing2' => 'nullable|string|max:255',
                'alamat_pembimbing2' => 'nullable|string|max:255',
                'tugas_pembimbing2' => 'nullable|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'nim_nik' => 'required|string|max:255',
                'judul_skripsi' => 'required|string',
                'masa_penugasan' => 'required|string|max:255',
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

            $login = Auth::user()?->prodi ? Auth::user()->prodi->alias : 'UMUM';
            $no_surat = $validate['no_surat'];

            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('ST', $no_surat, date('Y-m-d'), $validate['prodi_id']);

            Log::info($validate['pembimbing1']);
            $st = new SuratTugas();
            $st->nomor = $formattedNoSurat;
            $st->pembimbing1 = $validate['pembimbing1'];
            $st->alamat_pembimbing1 = $validate['alamat_pembimbing1'];
            $st->tugas_pembimbing1 = $validate['tugas_pembimbing1'] ?? 'Pembimbing 1';
            $st->pembimbing2 = $validate['pembimbing2'] ?? '';
            $st->alamat_pembimbing2 = $validate['alamat_pembimbing2'] ?? '';
            $st->tugas_pembimbing2 = $validate['tugas_pembimbing2'] ?? '';
            $st->nama_mhs = $validate['nama_mhs'];
            $st->nim_nik = $validate['nim_nik'];
            $st->judul_skripsi = $validate['judul_skripsi'];
            $st->masa_penugasan = $validate['masa_penugasan'];
            $st->user_id = Auth::user()->id;
            $st->prodi_id = $validate['prodi_id'];
            $st->status = 'pending';
            $st->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';
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

            $this->generatePdfFile($st->id);

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
                'pembimbing1' => 'nullable|string|max:255',
                'alamat_pembimbing1' => 'nullable|string|max:255',
                'tugas_pembimbing1' => 'nullable|string|max:255',
                'pembimbing2' => 'nullable|string|max:255',
                'alamat_pembimbing2' => 'nullable|string|max:255',
                'tugas_pembimbing2' => 'nullable|string|max:255',
                'nama_mhs' => 'required|string|max:255',
                'nim_nik' => 'required|string|max:255',
                'judul_skripsi' => 'required|string',
                'masa_penugasan' => 'required|string|max:255',
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

            $st = SuratTugas::find($id);
            if (!$st) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $st->drive_file_id;
            $oldLocalPath = $st->local_path;


            $tugas_pembimbing2 = null;
            if (empty($validate['pembimbing2'])) {
                $tugas_pembimbing2 = null;
            } else {
                $tugas_pembimbing2 = 'Pembimbing 2';
            }

            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('ST', $validate['no_surat'], date('Y-m-d'), $validate['prodi_id'] ?? null);
            $st->nomor = $formattedNoSurat;
            $st->pembimbing1 = $validate['pembimbing1'];
            $st->alamat_pembimbing1 = $validate['alamat_pembimbing1'];
            $st->tugas_pembimbing1 = $validate['tugas_pembimbing1'] ?? 'Pembimbing 1';
            $st->pembimbing2 = $validate['pembimbing2'] ?? '';
            $st->alamat_pembimbing2 = $validate['alamat_pembimbing2'] ?? '';
            $st->tugas_pembimbing2 = $tugas_pembimbing2 ?? '';
            $st->nama_mhs = $validate['nama_mhs'];
            $st->nim_nik = $validate['nim_nik'];
            $st->judul_skripsi = $validate['judul_skripsi'];
            $st->masa_penugasan = $validate['masa_penugasan'];
            $st->prodi_id = $validate['prodi_id'];
            $st->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';

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

            $this->generatePdfFile($st->id);

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
            $data = SuratTugas::find($id);

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $path = $this->generatePdfFile($data->id);

            if (empty($path) || !file_exists($path)) {
                return response()->json(['status' => false, 'message' => 'File PDF tidak ditemukan di server'], 404);
            }

            $fileName = basename($path);

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }

    public function generatePdfFile($id): ?string
    {
        try {
            $st = SuratTugas::find($id);
            if (!$st) {
                return null;
            }

            $data = SuratTugas::leftJoin('prodi', 'prodi.id', '=', 'surat_tugas.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_tugas.*',
                    'prodi.nama as prodi_name',
                    'fakultas.dekan as nama_kepala',
                    'fakultas.nidn_dekan as nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd')
                )
                ->where('surat_tugas.id', $st->id)
                ->first();

            if (!$data) {
                return null;
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

            $tddBase64 = '';
            $stempelBase64 = '';

            if (isset($data->petanda_tangan) && in_array($data->petanda_tangan, ['ya', 'stempel'])) {
                if ($data->petanda_tangan === 'ya' && !empty($data->ttd)) {
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

            $dosens = [
                [
                    'nama' => $data->pembimbing1 ?? '',
                    'alamat' => $data->alamat_pembimbing1 ?? '',
                    'tugas' => $data->tugas_pembimbing1 ?? 'Pembimbing 1',
                ]
            ];
            if (!empty($data->pembimbing2)) {
                $dosens[] = [
                    'nama' => $data->pembimbing2,
                    'alamat' => $data->alamat_pembimbing2 ?? '',
                    'tugas' => $data->tugas_pembimbing2 ?? '',
                ];
            }

            $rawJudul = trim($data->judul_skripsi ?? '');
            $cleanJudul = trim($rawJudul, '"\' ');
            $hasArabic = preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $cleanJudul);
            if ($hasArabic && class_exists('\ArPHP\I18N\Arabic')) {
                $arabic = new \ArPHP\I18N\Arabic();
                $glyphs = $arabic->utf8Glyphs($cleanJudul);
                $formattedJudul = str_replace(["\r\n", "\r", "\n"], ' ', trim($glyphs));
            } else {
                $formattedJudul = '"' . trim(mb_strtoupper($cleanJudul, 'UTF-8')) . '"';
            }

            $pdfData = [
                'nomor' => $data->nomor,
                'dosens' => $dosens,
                'nama_mhs' => $data->nama_mhs ?? '',
                'nim_nik' => $data->nim_nik ?? '',
                'fakultas_prodi' => $data->prodi_name ?? 'UMUM',
                'fakultas' => $data->fakultas_name ?? 'UMUM',
                'judul_skripsi' => $formattedJudul,
                'masa_penugasan' => $data->masa_penugasan ?? '',
                'tanggal' => \App\Services\SuratService::formatTanggalIndonesian(date('Y-m-d')),
                'nama_kepala' => $data->nama_kepala ?? '',
                'nidn_kepala' => $data->nidn_kepala ?? '',
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'lembaga_pemberi_tugas' => "Fakultas " . ($data->fakultas_name ?? 'UMUM') . " Universitas Islam Internasional Darullughah Wadda'wah",
                'stempel' => $stempelBase64,
                'petanda_tangan' => $data->petanda_tangan ?? 'tidak',
            ];

            $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratTugasController/');
            $pdf = Pdf::loadView('pdf.surat_tugas', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_tugas_' . ($data->nim_nik ?? 'doc') . '_' . uniqid() . '.pdf';

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);
            SuratTugas::where('id', $st->id)->update(['local_path' => $path]);

            $nameTable = 'Surat Tugas';
            UploudSuratToDrive::dispatch($st->id, $nameTable, $data->prodi_name ?? 'UMUM', SuratTugas::class);

            return $path;
        } catch (\Throwable $e) {
            Log::error("SuratTugas generatePdfFile error: " . $e->getMessage() . " on line " . $e->getLine());
            return null;
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

    public function getDosen(Request $request)
    {
        try {
            $search = $request->search ?? '';
            $dosen = \App\Services\DosenService::searchDosen($search);
            return response()->json(['status' => true, 'data' => $dosen]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Gagal ambil data dosen']);
        }
    }
}
