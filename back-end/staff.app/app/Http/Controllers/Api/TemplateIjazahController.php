<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TemplateIjazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TemplateIjazahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info($request->all());

        $data = TemplateIjazah::leftJoin('prodi', 'prodi.id', '=', 'template_ijazah.prodi_id')
            ->select('template_ijazah.*', 'prodi.nama as nama_prodi');

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('template_ijazah.nama_template', 'LIKE', "%{$search}%");
                $q->orWhere('template_ijazah.jenjang', 'LIKE', "%{$search}%");
                $q->orWhere('template_ijazah.ukuran_kertas', 'LIKE', "%{$search}%");
                $q->orWhere('prodi.nama', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('prodi_id')) {
            $data->where('prodi_id', $request->prodi_id);
        }

        if ($request->filled('jenjang')) {
            $data->where('jenjang', $request->jenjang);
        }

        if ($request->filled('is_active')) {
            $data->where('is_active', $request->is_active);
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
                'prodi_id'         => 'nullable|integer|exists:prodi,id',
                'jenjang'          => 'nullable|in:D3,D4,S1,S2,S3',
                'nama_template'    => 'required|string|max:100',
                'file_background'  => 'nullable',
                'ukuran_kertas'    => 'required|in:A4,A3,Legal,F4',
                'orientasi'        => 'required|in:portrait,landscape',
                'is_active'        => 'required|in:aktif,tidak',
                'teks_statis'      => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $templateIjazah = new TemplateIjazah();
            $templateIjazah->prodi_id = $validate['prodi_id'] ?? null;
            $templateIjazah->jenjang = $validate['jenjang'] ?? null;
            $templateIjazah->nama_template = $validate['nama_template'];

            if ($request->hasFile('file_background')) {
                $path = $request->file('file_background')->store('template_ijazah', 'public');
                $templateIjazah->file_background = $path;
            } else {
                $templateIjazah->file_background = $validate['file_background'] ?? null;
            }

            $templateIjazah->ukuran_kertas = $validate['ukuran_kertas'];
            $templateIjazah->orientasi = $validate['orientasi'];
            $templateIjazah->is_active = $validate['is_active'] ?? 'aktif';

            if (isset($validate['teks_statis'])) {
                $teksStatis = $validate['teks_statis'];
                if (is_string($teksStatis)) {
                    $templateIjazah->teks_statis = json_decode($teksStatis, true);
                } else {
                    $templateIjazah->teks_statis = $teksStatis;
                }
            }

            $templateIjazah->user_id = Auth::user()->id;
            $templateIjazah->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $templateIjazah = TemplateIjazah::leftJoin('prodi', 'prodi.id', '=', 'template_ijazah.prodi_id')
                ->select('template_ijazah.*', 'prodi.nama as nama_prodi')
                ->where('template_ijazah.id', $id)
                ->first();

            if (!$templateIjazah) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            // Load positional fields from posisi_template and convert back into expected JSON 
            $positions = \App\Models\PosisiTemplate::where('template_id', $id)->get();
            $fields_positions = [];
            foreach ($positions as $pos) {
                $fields_positions[$pos->field_name] = [
                    'x' => (int) $pos->posisi_x,
                    'y' => (int) $pos->posisi_y,
                    'fontSize' => (int) $pos->font_size,
                    'fontFamily' => $pos->font_family,
                    'alignment' => $pos->alignment,
                    'fontWeight' => $pos->font_weight
                ];
            }
            $data = $templateIjazah->toArray();
            $data['fields_positions'] = $fields_positions;

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diambil'
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('=== UPDATE TEMPLATE IJAZAH ===');
        Log::info('ID: ' . $id);
        Log::info('Request data:', $request->all());

        try {
            $validator = Validator::make($request->all(), [
                'prodi_id'         => 'nullable|integer|exists:prodi,id',
                'jenjang'          => 'nullable|in:D3,D4,S1,S2,S3',
                'nama_template'    => 'required|string|max:100',
                'file_background'  => 'nullable',
                'ukuran_kertas'    => 'required|in:A4,A3,Legal,F4',
                'orientasi'        => 'required|in:portrait,landscape',
                'is_active'        => 'required|in:aktif,tidak',
                'fields_positions' => 'nullable|string',
                'teks_statis'      => 'nullable',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            Log::info('Validated data:', $validate);

            $templateIjazah = TemplateIjazah::find($id);

            if (!$templateIjazah) {
                Log::error('Template not found with ID: ' . $id);
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            Log::info('Existing data before update:', $templateIjazah->toArray());

            // Update fields
            if (array_key_exists('prodi_id', $validate)) {
                $templateIjazah->prodi_id = $validate['prodi_id'];
            } else if ($request->has('prodi_id') && empty($request->prodi_id)) {
                $templateIjazah->prodi_id = null;
            }

            if (array_key_exists('jenjang', $validate)) {
                $templateIjazah->jenjang = $validate['jenjang'];
            } else if ($request->has('jenjang') && empty($request->jenjang)) {
                $templateIjazah->jenjang = null;
            }

            $templateIjazah->nama_template = $validate['nama_template'];

            if ($request->hasFile('file_background')) {
                $path = $request->file('file_background')->store('template_ijazah', 'public');
                $templateIjazah->file_background = $path;
            } else {
                // If it's a string, it might be the existing path or null
                $templateIjazah->file_background = $validate['file_background'] ?? $templateIjazah->file_background;
            }

            $templateIjazah->ukuran_kertas = $validate['ukuran_kertas'];
            $templateIjazah->orientasi = $validate['orientasi'];
            $templateIjazah->is_active = $validate['is_active'] ?? 'aktif';

            if (array_key_exists('teks_statis', $validate)) {
                $teksStatis = $validate['teks_statis'];
                Log::info('teks_statis received:', ['type' => gettype($teksStatis), 'value' => $teksStatis]);
                
                if ($teksStatis !== null && $teksStatis !== '' && $teksStatis !== '[]' && $teksStatis !== 'null') {
                    // Handle both array (from JSON body) and string (from form-data) inputs
                    if (is_string($teksStatis)) {
                        $decoded = json_decode($teksStatis, true);
                        $templateIjazah->teks_statis = $decoded;
                    } else if (is_array($teksStatis)) {
                        $templateIjazah->teks_statis = $teksStatis;
                    }
                } else {
                    $templateIjazah->teks_statis = null;
                }
                Log::info('teks_statis to save:', ['value' => $templateIjazah->teks_statis]);
            }




            $templateIjazah->user_id = Auth::user()->id;

            Log::info('Data to be saved:', $templateIjazah->toArray());

            $saved = $templateIjazah->save();

            // Save to posisi_template table
            if ($saved && array_key_exists('fields_positions', $validate) && $validate['fields_positions']) {
                $posRaw = $validate['fields_positions'];
                $positions = is_string($posRaw) ? json_decode($posRaw, true) : $posRaw;
                
                \Illuminate\Support\Facades\Log::info('DEBUG POSITIONS ARRAY:', is_array($positions) ? array_keys($positions) : []);
                file_put_contents(public_path('debug_positions.txt'), "Received fields:\n" . print_r(is_array($positions) ? array_keys($positions) : [], true));

                if (is_array($positions)) {
                    $keysToKeep = array_keys($positions);
                    \App\Models\PosisiTemplate::where('template_id', $templateIjazah->id)
                        ->whereNotIn('field_name', $keysToKeep)
                        ->delete();

                    $urutan = 1;
                    foreach ($positions as $key => $pos) {
                        \App\Models\PosisiTemplate::updateOrCreate(
                            [
                                'template_id' => $templateIjazah->id,
                                'field_name' => $key,
                            ],
                            [
                                'posisi_x' => $pos['x'] ?? 0,
                                'posisi_y' => $pos['y'] ?? 0,
                                'font_size' => $pos['fontSize'] ?? 12,
                                'font_family' => $pos['fontFamily'] ?? 'Arial',
                                'alignment' => $pos['alignment'] ?? 'left',
                                'font_weight' => $pos['fontWeight'] ?? 'normal',
                                'urutan' => $urutan++
                            ]
                        );
                    }
                    Log::info('Posisi template saved successfully for ' . count($positions) . ' fields');
                } else {
                    Log::error('Positions is not an array after decode: ', ['posRaw' => $posRaw]);
                }
            } else {
                Log::info('No fields_positions to save or conditions not met', [
                    'saved' => $saved,
                    'exists' => array_key_exists('fields_positions', $validate),
                    'val' => $validate['fields_positions'] ?? null
                ]);
            }


            Log::info('Save result: ' . ($saved ? 'SUCCESS' : 'FAILED'));
            Log::info('Updated data:', $templateIjazah->fresh()->toArray());

            if (!$saved) {
                Log::error('Failed to save template ijazah');
                return response()->json([
                    'status' => false,
                    'message' => 'Data gagal diupdate'
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::error('Exception during update:', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $templateIjazah = TemplateIjazah::find($id);

            if (!$templateIjazah) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $templateIjazah->delete();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ], 500);
        }
    }
}
