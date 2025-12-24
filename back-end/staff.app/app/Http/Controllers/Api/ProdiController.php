<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            $validate = $request->validate([
                'kode'      => 'nullable|string|max:50',
                'alias'     => 'nullable|string|max:5',
                'nama'      => 'nullable|string|max:75',
                'aktif'     => 'required|in:Y,T',
                'jenjang'   => 'nullable|string|in:S1,S2,S3',
                'nidn_kepala' => 'nullable|string|max:15',
                'nama_kepala' => 'nullable|string|max:60',
            ]);

            $prodi = new Prodi();
            $prodi->kode = $request->kode;
            $prodi->alias = $request->alias;
            $prodi->nama = $request->nama;
            $prodi->aktif = $request->aktif ?? 'T';
            $prodi->jenjang = $request->jenjang ?? 'S1';
            $prodi->nidn_kepala = $request->nidn_kepala;
            $prodi->nama_kepala = $request->nama_kepala;
            $prodi->user_id = auth()->user()->id;
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
        $prodi = Prodi::find($id);

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
            $validate = $request->validate([
                'kode'      => 'nullable|string|max:50',
                'alias'     => 'nullable|string|max:5',
                'nama'      => 'nullable|string|max:75',
                'aktif'     => 'required|in:Y,T',
                'jenjang'   => 'nullable|string|in:S1,S2,S3',
                'nidn_kepala' => 'nullable|string|max:15',
                'nama_kepala' => 'nullable|string|max:60',
            ]);

            $prodi = Prodi::find($id);

            if (!$prodi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $prodi->kode = $request->kode;
            $prodi->alias = $request->alias;
            $prodi->nama = $request->nama;
            $prodi->aktif = $request->aktif ?? 'T';
            $prodi->jenjang = $request->jenjang ?? 'S1';
            $prodi->nidn_kepala = $request->nidn_kepala;
            $prodi->nama_kepala = $request->nama_kepala;
            $prodi->user_id = auth()->user()->id;
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
