<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\FakultasProdi;
use App\Models\Fakultas;
use App\Models\Prodi;

class FakultasController extends Controller
{
    public function index(Request $request)
    {
        $data = Fakultas::query();
        $data->select(
            'fakultas.id',
            'fakultas.nama as nama_fakultas',
            'fakultas.kode as kode_fakultas',
            'fakultas.dekan as dekan_fakultas',
            'fakultas.tanda_tangan_id'
        );

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('dekan', 'LIKE', "%{$search}%");
                $q->orWhere('nama', 'LIKE', "%{$search}%");
                $q->orWhere('kode', 'LIKE', "%{$search}%");

                // Search in related prodis via pivot exists subquery
                $q->orWhereExists(function ($subq) use ($search) {
                    $subq->select('fakultas_prodi.id')
                        ->from('fakultas_prodi')
                        ->join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id')
                        ->whereColumn('fakultas_prodi.fakultas_id', 'fakultas.id')
                        ->where(function ($q2) use ($search) {
                            $q2->where('prodi.nama', 'LIKE', "%{$search}%")
                                ->orWhere('prodi.alias', 'LIKE', "%{$search}%")
                                ->orWhere('prodi.nama_kepala', 'LIKE', "%{$search}%");
                        });
                });
            });
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'asc')
        );

        $data = $data->paginate($request->input('limit', 10));

        // Append prodi list string to each result for display
        $data->getCollection()->transform(function ($fakultas) {
            $prodis = FakultasProdi::join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id')
                ->where('fakultas_prodi.fakultas_id', $fakultas->id)
                ->pluck('prodi.nama')
                ->implode(', ');

            $ttdIds = is_string($fakultas->tanda_tangan_id) ? json_decode($fakultas->tanda_tangan_id, true) : $fakultas->tanda_tangan_id;
            if (is_array($ttdIds) && count($ttdIds) > 0) {
                $ttdNames = \App\Models\TandaTangan::whereIn('id', $ttdIds)->pluck('nama')->implode(', ');
            } else {
                $ttdNames = null;
            }

            $fakultas->nama_ttd = $ttdNames;
            $fakultas->nama_prodi = $prodis;
            return $fakultas;
        });

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
        // Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'kode_fakultas' => 'required',
                'nama_fakultas' => 'required',
                'dekan'        => 'required',
                'nidn_dekan'   => 'nullable|string|max:100',
                'tanda_tangan_id' => 'nullable|array',
                'tanda_tangan_id.*' => 'exists:tanda_tangan,id',
                'prodi'        => 'required|array',
                'prodi.*.id'   => 'required|exists:prodi,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $fakultas        = new Fakultas();
            $fakultas->nama  = $validate['nama_fakultas'];
            $fakultas->kode  = $validate['kode_fakultas'];
            $fakultas->dekan = $validate['dekan'];
            $fakultas->nidn_dekan = $validate['nidn_dekan'] ?? null;
            $ttd_ids = null;
            if (isset($validate['tanda_tangan_id']) && is_array($validate['tanda_tangan_id'])) {
                $ttd_ids = array_map(function($item) {
                    return is_array($item) ? $item['id'] : $item;
                }, $validate['tanda_tangan_id']);
            }
            $fakultas->tanda_tangan_id = $ttd_ids;
            $fakultas->save();

            foreach ($request->prodi as $selectedProdi) {
                $fakultasProdi              = new FakultasProdi();
                $fakultasProdi->fakultas_id = $fakultas->id;
                $fakultasProdi->prodi_id    = $selectedProdi['id'];
                $fakultasProdi->save();
            }

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

    public function show($id)
    {
        $fakultas = Fakultas::find($id);

        if (!$fakultas) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $linkedProdis = FakultasProdi::join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id')
            ->where('fakultas_prodi.fakultas_id', $fakultas->id)
            ->select('prodi.*')
            ->get();

            $ttdData = [];
            $ttdIds = is_string($fakultas->tanda_tangan_id) ? json_decode($fakultas->tanda_tangan_id, true) : $fakultas->tanda_tangan_id;
            if (is_array($ttdIds) && count($ttdIds) > 0) {
                $ttdData = \App\Models\TandaTangan::whereIn('id', $ttdIds)->get();
            }

            $data = [
                'id' => $fakultas->id,
                'nama_fakultas' => $fakultas->nama,
                'kode_fakultas' => $fakultas->kode,
                'dekan' => $fakultas->dekan,
                'nidn_dekan' => $fakultas->nidn_dekan,
                'tanda_tangan_id' => $ttdData,
                'prodi' => $linkedProdis
            ];

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        // Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'kode_fakultas' => 'required',
                'nama_fakultas' => 'required',
                'dekan'        => 'required',
                'nidn_dekan'   => 'nullable|string|max:100',
                'tanda_tangan_id' => 'nullable|array',
                'tanda_tangan_id.*' => 'exists:tanda_tangan,id',
                'prodi'        => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $fakultas = Fakultas::find($id);

            if (!$fakultas) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // Update Fakultas
            $fakultas->nama  = $validate['nama_fakultas'];
            $fakultas->kode  = $validate['kode_fakultas'];
            $fakultas->dekan = $validate['dekan'];
            $fakultas->nidn_dekan = $validate['nidn_dekan'] ?? $fakultas->nidn_dekan;
            $ttd_ids = $fakultas->tanda_tangan_id;
            if (array_key_exists('tanda_tangan_id', $validate)) {
                if (is_array($validate['tanda_tangan_id'])) {
                    $ttd_ids = array_map(function($item) {
                        return is_array($item) ? $item['id'] : $item;
                    }, $validate['tanda_tangan_id']);
                } else {
                    $ttd_ids = null;
                }
            }
            $fakultas->tanda_tangan_id = $ttd_ids;
            $fakultas->save();

            // Sync Prodis (Delete all, create new)
            FakultasProdi::where('fakultas_id', $fakultas->id)->delete();

            foreach ($request->prodi as $selectedProdi) {
                $newFp              = new FakultasProdi();
                $newFp->fakultas_id = $fakultas->id;
                $newFp->prodi_id    = $selectedProdi['id'];
                $newFp->save();
            }

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

    public function destroy($id)
    {
        try {
            $fakultas = Fakultas::find($id);
            if ($fakultas) {
                FakultasProdi::where('fakultas_id', $fakultas->id)->delete();
                $fakultas->delete();
            }

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
