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

            // Penomoran Otomatis
            $aliasProdi = \App\Models\Prodi::find($validate['prodi_id'])->alias;
            $no = \App\Models\NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_next = str_pad($no + 1, 3, '0', STR_PAD_LEFT);
            $unit = 'K.' . strtoupper($aliasProdi);

            $nomorSurat = \App\Services\SuratService::NoHasilRapat($no_next, $unit);

            $hasilRapat = new HasilRapat();
            $hasilRapat->nomor_surat = $nomorSurat;
            $hasilRapat->prodi_id = $validate['prodi_id'];
            $hasilRapat->agenda = $validate['agenda'];
            $hasilRapat->tanggal = $validate['tanggal'];
            $hasilRapat->waktu = $validate['waktu'] ?? null;
            $hasilRapat->tempat = $validate['tempat'] ?? null;
            $hasilRapat->pembahasan = $validate['pembahasan'] ?? null;
            $hasilRapat->status = 'pending';
            $hasilRapat->save();

            // Simpan log nomor
            $newNo = new \App\Models\NoSurat();
            $newNo->nomor = $no_next;
            $newNo->user_id = Auth::user()->id;
            $newNo->save();

            // Simpan ke log surat
            $log = new \App\Models\LogSurat();
            $log->nomor = $no_next;
            $log->nomor_surat = $nomorSurat;
            $log->nama_surat = 'Hasil Rapat';
            $log->user_id = Auth::user()->id;
            $log->save();
            Log::info('masuk');
            if (!empty($validate['anggota_ids'])) {
                foreach ($validate['anggota_ids'] as $userId) {
                    $anggota = new AnggotaRapat();
                    $anggota->hasil_rapat_id = $hasilRapat->id;
                    $anggota->user_id = $userId;
                    $anggota->save();
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Data berhasil ditambahkan']);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th);
            return response()->json(['status' => false, 'message' => 'Data gagal ditambahkan']);
        }
    }

    public function show($id)
    {
        $hasilRapat = HasilRapat::with(['prodi', 'anggota.user'])->find($id);
        if (!$hasilRapat) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
        return response()->json(['status' => true, 'data' => $hasilRapat]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $hasilRapat = HasilRapat::find($id);
            if (!$hasilRapat) return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);

            $validator = Validator::make($request->all(), [
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

            $hasilRapat->agenda = $validate['agenda'];
            $hasilRapat->tanggal = $validate['tanggal'];
            $hasilRapat->waktu = $validate['waktu'] ?? null;
            $hasilRapat->tempat = $validate['tempat'] ?? null;
            $hasilRapat->pembahasan = $validate['pembahasan'] ?? null;
            $hasilRapat->user_id = Auth::user()->id;
            $hasilRapat->save();

            if (!empty($validate['anggota_ids'])) {
                AnggotaRapat::where('hasil_rapat_id', $id)->delete();
                foreach ($validate['anggota_ids'] as $userId) {
                    $anggota = new AnggotaRapat();
                    $anggota->hasil_rapat_id = $id;
                    $anggota->user_id = $userId;
                    $anggota->save();
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Data gagal diupdate']);
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
            $data = HasilRapat::with(['prodi'])->find($id);
            if (!$data) return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);

            $anggota = AnggotaRapat::with('user')->where('hasil_rapat_id', $id)->get();
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = file_exists($kopPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath)) : null;

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'agenda' => $data->agenda,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'waktu' => $data->waktu,
                'tempat' => $data->tempat,
                'pembahasan' => $data->pembahasan,
                'anggota' => $anggota,
                'kopBase64' => $kopBase64,
            ];

            $pdf = Pdf::loadView('pdf.v_hasil_rapat', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'hasil_rapat_' . str_replace('/', '_', $data->nomor_surat) . '.pdf';
            $directory = base_path('../public_html/pdf/');
            if (!file_exists($directory)) mkdir($directory, 0755, true);

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            // Upload ke Google Drive hanya jika file belum ada
            $nameTable = 'Hasil Rapat';
            $prodiName = $data->prodi->nama ?? 'Umum';
            $googlePath = $prodiName . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $prodiName, HasilRapat::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal generate PDF']);
        }
    }

    public function getProdi()
    {
        $login = Auth::user()->prodi;
        $prodi = $login ? \App\Models\Prodi::where('id', $login->id)->get() : \App\Models\Prodi::all();
        return response()->json(['status' => true, 'data' => $prodi]);
    }
}
