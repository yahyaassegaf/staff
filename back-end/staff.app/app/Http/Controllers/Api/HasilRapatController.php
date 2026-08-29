<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\HasilRapat;
use App\Models\AnggotaRapat;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HasilRapatController extends Controller
{
    public function index(Request $request)
    {
        Log::info('HasilRapat index request', [
            'user_id' => Auth::id(),
            'params' => $request->all(),
            'user_prodi_id' => Auth::user()->prodi_id,
            'user_level' => Auth::user()->level_id
        ]);
        $data = HasilRapat::leftJoin('prodi', 'prodi.id', '=', 'hasil_rapat.prodi_id');

        $data->select(
            'hasil_rapat.*',
            'prodi.nama as nama_prodi'
        );

        // Filter prodi dari request (umumnya oleh admin)
        if ($request->filled('prodi_id')) {
            $data->where('hasil_rapat.prodi_id', $request->prodi_id);
        }

        // Jika bukan admin (level_id != 2), filter berdasarkan prodi user yang login
        if (Auth::user()->level_id != 2) {
            $loginProdiId = Auth::user()->prodi_id;
            if ($loginProdiId) {
                $data->where('hasil_rapat.prodi_id', $loginProdiId);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('agenda', 'like', "%{$search}%")
                    ->orWhere('tempat', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $data->orderBy($request->input('sortBy', 'id'), $request->input('sortType', 'desc'));
        $data = $data->paginate($request->input('limit', 10));

        Log::info('HasilRapat data count: ' . $data->total());

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function store(Request $request)
    {
        Log::info($request->all());
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'no_surat' => 'nullable|string|max:255',
                'prodi_id' => 'required|exists:prodi,id',
                'agenda' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'waktu' => 'nullable',
                'tempat' => 'nullable|string|max:255',
                'pembahasan' => 'nullable|string',
                'anggota_ids' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $no_surat = $validate['no_surat'] ?? null;
            $nomorSurat = null;
            if ($no_surat) {
                $nomorSurat = \App\Services\SuratService::formatNomorSurat('STHR', $no_surat, $validate['tanggal'], $validate['prodi_id']);
            }

            $hasilRapat = new HasilRapat();
            $hasilRapat->nomor_surat = $nomorSurat;
            $hasilRapat->prodi_id = $validate['prodi_id'];
            $hasilRapat->agenda = $validate['agenda'];
            $hasilRapat->tanggal = $validate['tanggal'];
            $hasilRapat->waktu = $validate['waktu'] ?? null;
            $hasilRapat->tempat = $validate['tempat'] ?? null;
            $hasilRapat->pembahasan = $validate['pembahasan'] ?? null;
            $hasilRapat->status = 'pending';
            $hasilRapat->user_id = Auth::user()->id;
            $hasilRapat->save();
            if (!empty($validate['anggota_ids'])) {
                foreach ($validate['anggota_ids'] as $userId) {
                    $anggota = new AnggotaRapat();
                    $anggota->hasil_rapat_id = $hasilRapat->id;
                    $anggota->user_id = $userId;
                    $anggota->save();
                }
            }

            // Generate PDF dan simpan local_path (setelah commit agar data sudah final)
            $data = HasilRapat::with(['prodi'])->find($hasilRapat->id);
            if ($data) {
                $anggotaList = AnggotaRapat::with('user')->where('hasil_rapat_id', $hasilRapat->id)->get();
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);
                $formatSurat = \App\Models\JenisSurat::where('alias', 'STHR')->value('format_surat');

                $pdfData = [
                    'nomor_surat' => $data->nomor_surat ?? null,
                    'format_surat' => $formatSurat,
                    'agenda' => $data->agenda,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'waktu' => $data->waktu,
                    'tempat' => $data->tempat,
                    'pembahasan' => $data->pembahasan,
                    'anggota' => $anggotaList,
                    'kopBase64' => $kopBase64,
                ];

                $directory = base_path('../public_html/pdf/Umum/Hasil Rapat/');
                $pdf = Pdf::loadView('pdf.v_hasil_rapat', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'hasil_rapat_' . str_replace('/', '_', $data->nomor_surat ?? 'tanpa_nomor') . '_' . uniqid() . '.pdf';
                if (!file_exists($directory)) mkdir($directory, 0755, true);

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Hasil Rapat';
                $prodiName = $data->prodi->nama ?? 'Umum';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $prodiName, HasilRapat::class);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("HasilRapat Store Error: " . $th->getMessage() . " | Baris: " . $th->getLine() . " | File: " . $th->getFile());
            return response()->json(['status' => false, 'message' => 'Data gagal ditambahkan', 'error' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $hasilRapat = HasilRapat::with(['prodi', 'anggota.user'])->find($id);
        if (!$hasilRapat) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        // Ekstrak no_surat asli dari nomor_surat (misal "SU-69532/..." -> "69532")
        if ($hasilRapat->nomor_surat) {
            $parts = explode('/', $hasilRapat->nomor_surat);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $hasilRapat->no_surat = $firstPart;
        } else {
            $hasilRapat->no_surat = null;
        }

        return response()->json(['status' => true, 'data' => $hasilRapat]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $hasilRapat = HasilRapat::find($id);
            if (!$hasilRapat) return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);

            $oldDriveFileId = $hasilRapat->drive_file_id;
            $oldLocalPath = $hasilRapat->local_path;

            $validator = Validator::make($request->all(), [
                'no_surat' => 'nullable|string|max:255',
                'agenda' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'waktu' => 'nullable',
                'tempat' => 'nullable|string|max:255',
                'pembahasan' => 'nullable|string',
                'anggota_ids' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $no_surat = $validate['no_surat'] ?? null;
            if ($no_surat) {
                $hasilRapat->nomor_surat = \App\Services\SuratService::formatNomorSurat('STHR', $no_surat, $validate['tanggal'], $hasilRapat->prodi_id);
            }
            $hasilRapat->agenda = $validate['agenda'];
            $hasilRapat->tanggal = $validate['tanggal'];
            $hasilRapat->waktu = $validate['waktu'] ?? null;
            $hasilRapat->tempat = $validate['tempat'] ?? null;
            $hasilRapat->pembahasan = $validate['pembahasan'] ?? null;
            $hasilRapat->user_id = Auth::user()->id;

            if (!empty($validate['anggota_ids'])) {
                AnggotaRapat::where('hasil_rapat_id', $id)->delete();
                foreach ($validate['anggota_ids'] as $userId) {
                    $anggota = new AnggotaRapat();
                    $anggota->hasil_rapat_id = $id;
                    $anggota->user_id = $userId;
                    $anggota->save();
                }
            }

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $hasilRapat->drive_file_id = null;
            $hasilRapat->drive_link = null;
            $hasilRapat->status = 'pending';
            $hasilRapat->save();

            // Re-fetch record with joins to build the new PDF
            $data = HasilRapat::with(['prodi'])->find($id);
            if ($data) {
                $anggota = AnggotaRapat::with('user')->where('hasil_rapat_id', $id)->get();
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);
                $formatSurat = \App\Models\JenisSurat::where('alias', 'STHR')->value('format_surat');

                $pdfData = [
                    'nomor_surat' => $data->nomor_surat ?? null,
                    'format_surat' => $formatSurat,
                    'agenda' => $data->agenda,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'waktu' => $data->waktu,
                    'tempat' => $data->tempat,
                    'pembahasan' => $data->pembahasan,
                    'anggota' => $anggota,
                    'kopBase64' => $kopBase64,
                ];

                $directory = base_path('../public_html/pdf/Umum/Hasil Rapat/');
                $pdf = Pdf::loadView('pdf.v_hasil_rapat', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'hasil_rapat_' . str_replace('/', '_', $data->nomor_surat ?? 'tanpa_nomor') . '_' . uniqid() . '.pdf';
                if (!file_exists($directory)) mkdir($directory, 0755, true);

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Hasil Rapat';
                $prodiName = $data->prodi->nama ?? 'Umum';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $prodiName, HasilRapat::class);
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("HasilRapat Update Error: " . $th->getMessage() . " | Baris: " . $th->getLine() . " | File: " . $th->getFile());
            return response()->json(['status' => false, 'message' => 'Data gagal diupdate', 'error' => $th->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $hasilRapat = HasilRapat::find($id);
        if (!$hasilRapat) return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        $hasilRapat->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }

    public function downloadPdf($id)
    {
        try {
            Log::info('downloadPdf called', ['id' => $id]);

            $data = HasilRapat::find($id);

            if (!$data) {
                Log::warning('downloadPdf: Data tidak ditemukan', ['id' => $id]);
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            Log::info('downloadPdf: Data found', [
                'id' => $data->id,
                'local_path' => $data->local_path,
                'drive_link' => $data->drive_link,
            ]);

            if (empty($data->local_path)) {
                return response()->json([
                    'status' => false,
                    'message' => 'File PDF belum digenerate. Path kosong.'
                ], 404);
            }

            if (!file_exists($data->local_path)) {
                Log::warning('downloadPdf: File tidak ditemukan di disk', ['path' => $data->local_path]);
                return response()->json([
                    'status' => false,
                    'message' => 'File PDF tidak ditemukan di server. Path: ' . $data->local_path
                ], 404);
            }

            $fileName = basename($data->local_path);

            return response()->file($data->local_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error('downloadPdf error', [
                'id' => $id,
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Gagal download PDF: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getProdi()
    {
        $login = Auth::user()->prodi;
        $prodi = $login ? \App\Models\Prodi::where('id', $login->id)->get() : \App\Models\Prodi::all();
        return response()->json(['status' => true, 'data' => $prodi]);
    }
}
