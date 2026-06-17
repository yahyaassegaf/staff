<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProdiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info($request->all());

        $data = Prodi::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nama', 'LIKE', "%{$search}%");
                $q->orWhere('kode', 'LIKE', "%{$search}%");
                $q->orWhere('alias', 'LIKE', "%{$search}%");
            });
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'asc')
        );

        $data = $data->paginate($request->input('limit', 10));

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info($request->all());

        try {
            $validator = Validator::make($request->all(), [
                'kode'      => 'nullable|string|max:50',
                'alias'     => 'nullable|string|max:5',
                'nama'      => 'nullable|string|max:75',
                'aktif'     => 'required|in:Y,T',
                'jenjang'   => 'nullable|string|in:S1,S2,S3',
                'gelar'     => 'nullable|string|max:50',
                'nidn_kepala' => 'nullable|string|max:15',
                'nama_kepala' => 'nullable|string|max:60',
                'tanda_tangan' => 'nullable|integer|exists:tanda_tangan,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $prodi = new Prodi();
            $prodi->kode = $validate['kode'] ?? null;
            $prodi->alias = $validate['alias'] ?? null;
            $prodi->nama = $validate['nama'] ?? null;
            $prodi->aktif = $validate['aktif'] ?? 'T';
            $prodi->jenjang = $validate['jenjang'] ?? 'S1';
            $prodi->gelar = $validate['gelar'] ?? null;
            $prodi->nidn_kepala = $validate['nidn_kepala'] ?? null;
            $prodi->nama_kepala = $validate['nama_kepala'] ?? null;
            $prodi->user_id = Auth::user()->id;
            $prodi->tanda_tangan_id = $validate['tanda_tangan'] ?? null;
            $prodi->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Log::info($id);
        $prodi = Prodi::leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
        ->select('prodi.*', 'tanda_tangan.nama as tanda_tangan')
        ->where('prodi.id', $id)
        ->first();
        // Log::info($prodi);
        if (!$prodi) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $prodi,
            'message' => 'Data berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info($request->all());

        try {
            $validator = Validator::make($request->all(), [
                'kode'      => 'nullable|string|max:50',
                'alias'     => 'nullable|string|max:5',
                'nama'      => 'nullable|string|max:75',
                'aktif'     => 'required|in:Y,T',
                'jenjang'   => 'nullable|string|in:S1,S2,S3',
                'gelar'     => 'nullable|string|max:50',
                'nidn_kepala' => 'nullable|string|max:15',
                'nama_kepala' => 'nullable|string|max:60',
                'tanda_tangan' => 'nullable|integer|exists:tanda_tangan,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $prodi = Prodi::find($id);

            if (!$prodi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            Log::info('validated', $validate);

            $prodi->kode = $validate['kode'] ?? null;
            $prodi->alias = $validate['alias'] ?? null;
            $prodi->nama = $validate['nama'] ?? null;
            $prodi->aktif = $validate['aktif'] ?? 'T';
            $prodi->jenjang = $validate['jenjang'] ?? 'S1';
            $prodi->gelar = $validate['gelar'] ?? null;
            $prodi->nidn_kepala = $validate['nidn_kepala'] ?? null;
            $prodi->nama_kepala = $validate['nama_kepala'] ?? null;
            $prodi->user_id = Auth::user()->id;
            $prodi->tanda_tangan_id = $validate['tanda_tangan'] ?? null;
            $prodi->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $prodi = Prodi::find($id);

            if (!$prodi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $prodi->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }
}
