<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Transkip;
use App\Models\Predikat;
use App\Models\TanggalNilai;
use App\Models\NilaiMahasiswa;
use App\Services\TranskipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TranskipController extends Controller
{
    /**
     * Get list of mahasiswa with transkip info
     */
    public function index(Request $request)
    {
        $data = Mahasiswa::with(['prodi', 'transkip']);

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('nim', 'LIKE', "%{$search}%")
                    ->orWhere('nik', 'LIKE', "%{$search}%");
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

    /**
     * Get detail mahasiswa, transkip, local saved nilai, and nilai from SIAKAD
     */
    public function show($id)
    {
        $mahasiswa = Mahasiswa::with(['prodi.fakultasProdi.fakultas', 'transkip', 'nilaiMahasiswa.tanggalNilai'])->find($id);

        if (!$mahasiswa) {
            return response()->json(['status' => false, 'message' => 'Mahasiswa tidak ditemukan'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $mahasiswa,
            'message' => 'Data berhasil diambil'
        ]);
    }

    /**
     * Fetch nilai from SIAKAD API based on NIM
     */
    public function getNilai($id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if (!$mahasiswa || !$mahasiswa->nim) {
            return response()->json(['status' => false, 'message' => 'Mahasiswa atau NIM tidak ditemukan'], 404);
        }

        try {
            $data = TranskipService::getNilaiByNim($mahasiswa->nim);

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Nilai dari SIAKAD berhasil diambil'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data dari SIAKAD: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force refresh cache SIAKAD
     */
    public function refreshNilai($id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if (!$mahasiswa || !$mahasiswa->nim) {
            return response()->json([
                'status' => false,
                'message' => 'Mahasiswa atau NIM tidak ditemukan'
            ], 404);
        }

        TranskipService::clearCache($mahasiswa->nim);
        $data = TranskipService::getNilaiByNim($mahasiswa->nim);

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Cache berhasil direfresh'
        ]);
    }

    /**
     * Store or update transkip data
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'ipk' => 'nullable|numeric',
            'judul_skripsi' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $predikat_kelulusan = null;
        if ($request->filled('ipk')) {
            $predikat_kelulusan = Predikat::getPredikat($request->ipk);
        }

        $transkip = Transkip::updateOrCreate(
            ['mahasiswa_id' => $request->mahasiswa_id],
            [
                'ipk' => $request->ipk,
                'judul_skripsi' => $request->judul_skripsi,
                'predikat_kelulusan' => $predikat_kelulusan
            ]
        );

        return response()->json([
            'status' => true,
            'data' => $transkip,
            'message' => 'Data transkrip berhasil disimpan'
        ]);
    }

    /**
     * Store selected nilai to local db
     */
    public function storeNilai(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'tanggal_nilai' => 'required|date',
            'nilai' => 'required|array',
            'nilai.*.kode_mk' => 'required|string',
            'nilai.*.nama_mk' => 'required|string',
            'nilai.*.sks_mk' => 'nullable|integer',
            'nilai.*.smt_mk' => 'nullable|integer',
            'nilai.*.nilai_akhir' => 'nullable|numeric',
            'nilai.*.nilai_bobot' => 'nullable|numeric',
            'nilai.*.nilai_huruf' => 'nullable|string',
            'nilai.*.transkrip' => 'nullable|in:Y,T'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $mahasiswa = Mahasiswa::find($request->mahasiswa_id);
            $nim = $mahasiswa ? $mahasiswa->nim : null;
            $nama_mhs = $mahasiswa ? $mahasiswa->nama : null;

            // Get or create tanggal
            $tanggal = TanggalNilai::firstOrCreate([
                'tanggal' => $request->tanggal_nilai,
                'nim' => $nim
            ]);

            // Clear old values for this student to avoid duplicates or keep them?
            // Usually we replace all selected for this student
            NilaiMahasiswa::where('mahasiswa_id', $request->mahasiswa_id)->delete();

            $insertData = [];
            foreach ($request->nilai as $n) {
                $insertData[] = [
                    'tanggal_nilai_id' => $tanggal->id,
                    'mahasiswa_id' => $request->mahasiswa_id,
                    'nim' => $nim,
                    'nama_mhs' => $nama_mhs,
                    'kode_mk' => $n['kode_mk'],
                    'nama_mk' => $n['nama_mk'],
                    'sks_mk' => $n['sks_mk'] ?? null,
                    'smt_mk' => $n['smt_mk'] ?? null,
                    'nilai_akhir' => $n['nilai_akhir'] ?? null,
                    'nilai_bobot' => $n['nilai_bobot'] ?? null,
                    'nilai_huruf' => $n['nilai_huruf'] ?? null,
                    'transkrip' => $n['transkrip'] ?? 'Y',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            NilaiMahasiswa::insert($insertData);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Nilai mahasiswa berhasil disimpan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of predikat
     */
    public function getPredikat()
    {
        $data = Predikat::orderBy('nilai_min', 'desc')->get();
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data predikat berhasil diambil'
        ]);
    }
}
