<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SettingJabatan;
use App\Models\TandaTangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SettingJabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info('SettingJabatan index request', [
            'user_id' => Auth::id(),
            'params' => $request->all()
        ]);
        
        $data = SettingJabatan::with('tandaTangan');

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where('kunci_jabatan', 'LIKE', "%{$search}%")
                 ->orWhere('nama_jabatan', 'LIKE', "%{$search}%");
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'desc')
        );

        $data = $data->paginate($request->input('limit', 10));

        Log::info('SettingJabatan data count: ' . $data->total());

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
        Log::info('SettingJabatan store', $request->all());
        
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'kunci_jabatan' => 'required|string|max:255|unique:setting_jabatan,kunci_jabatan',
                'nama_jabatan' => 'required|string|max:255',
                'nidn' => 'nullable|string|max:255',
                'nama_tanda_tangan' => 'required|string|max:255',
                'tdd' => 'nullable|string',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            // 1. Save to TandaTangan
            $tandaTangan = new TandaTangan();
            $tandaTangan->nama = $validate['nama_tanda_tangan'];
            $tandaTangan->tdd = $validate['tdd'] ?? null;
            $tandaTangan->user_id = Auth::user()->id;

            if ($request->hasFile('gambar')) {
                $dir = base_path('../public_html/tdd/');
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file = $request->file('gambar');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($dir, $filename);
                $tandaTangan->gambar = 'tdd/' . $filename;
            }
            $tandaTangan->save();

            // 2. Save to SettingJabatan
            $settingJabatan = new SettingJabatan();
            $settingJabatan->kunci_jabatan = $validate['kunci_jabatan'];
            $settingJabatan->nama_jabatan = $validate['nama_jabatan'];
            $settingJabatan->nidn = $validate['nidn'] ?? null;
            $settingJabatan->tanda_tangan_id = $tandaTangan->id;
            $settingJabatan->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
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
        $data = SettingJabatan::with('tandaTangan')->find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'kunci_jabatan' => 'required|string|max:255|unique:setting_jabatan,kunci_jabatan,'.$id,
                'nama_jabatan' => 'required|string|max:255',
                'nidn' => 'nullable|string|max:255',
                'nama_tanda_tangan' => 'required|string|max:255',
                'tdd' => 'nullable|string',
                'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $settingJabatan = SettingJabatan::find($id);

            if (!$settingJabatan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $settingJabatan->kunci_jabatan = $validate['kunci_jabatan'];
            $settingJabatan->nama_jabatan = $validate['nama_jabatan'];
            if (array_key_exists('nidn', $validate)) {
                $settingJabatan->nidn = $validate['nidn'];
            }
            
            // Update TandaTangan
            $tandaTangan = TandaTangan::find($settingJabatan->tanda_tangan_id);
            if ($tandaTangan) {
                $tandaTangan->nama = $validate['nama_tanda_tangan'];
                if (isset($validate['tdd'])) {
                    $tandaTangan->tdd = $validate['tdd'];
                }

                if ($request->hasFile('gambar')) {
                    // Optional: Delete old image if exists
                    if ($tandaTangan->gambar && file_exists(base_path('../public_html/'.$tandaTangan->gambar))) {
                        unlink(base_path('../public_html/'.$tandaTangan->gambar));
                    }
                    // Some local envs might use public_path. I will check what's in TandaTanganController
                    // Wait, TandaTanganController index uses $data->gambar = 'tdd/' . $filename;
                    // and store uses base_path('../public_html/tdd/') or public_path('tanda_tangan').
                    // TandaTanganController line 161 uses public_path('tanda_tangan').
                    // But store line 75 uses base_path('../public_html/tdd/'). I will use public_path to be safe or base_path.
                    $dir = base_path('../public_html/tdd/');
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $file = $request->file('gambar');
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($dir, $filename);
                    $tandaTangan->gambar = 'tdd/' . $filename;
                }
                $tandaTangan->save();
            }

            $settingJabatan->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
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
        DB::beginTransaction();
        try {
            $settingJabatan = SettingJabatan::find($id);

            if (!$settingJabatan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $tandaTanganId = $settingJabatan->tanda_tangan_id;
            
            $settingJabatan->delete();

            // Opsional: Hapus juga TandaTangan jika tidak ada yang lain mereferensikannya, 
            // namun untuk amannya kita hanya menghapus setting jabatannya saja atau menghapus tanda tangannya.
            // Asumsi kita hapus juga tanda tangannya.
            $tandaTangan = TandaTangan::find($tandaTanganId);
            if ($tandaTangan) {
                $tandaTangan->delete();
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }
}
