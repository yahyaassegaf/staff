<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MahasiswaImport;
use Illuminate\Support\Facades\Log;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $data = Mahasiswa::with('prodi');

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%");
                $q->orWhere('nim', 'LIKE', "%{$search}%");
                $q->orWhere('nik', 'LIKE', "%{$search}%");
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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'nik' => 'required|string|max:255',
                'tgl_lahir' => 'required|string|max:255',
                'nilai_akreditasi' => 'required|string|max:255',
                'nomor_sk_ban_pt' => 'required|string|max:255',
                'nomor_ijazah_nasional' => 'required|string|max:255',
                'tanggal_sk_yudisium' => 'required|string|max:255',
                'tanggal_penerbitan' => 'required|string|max:255',
                'prodi_id' => 'required|exists:prodi,id',
                'status' => 'required|in:belum,sudah',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            Mahasiswa::create($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $mahasiswa = Mahasiswa::find($id);

        if (!$mahasiswa) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $mahasiswa, 'message' => 'Data berhasil diambil']);
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'nik' => 'required|string|max:255',
                'tgl_lahir' => 'required|string|max:255',
                'nilai_akreditasi' => 'required|string|max:255',
                'nomor_sk_ban_pt' => 'required|string|max:255',
                'nomor_ijazah_nasional' => 'required|string|max:255',
                'tanggal_sk_yudisium' => 'required|string|max:255',
                'tanggal_penerbitan' => 'required|string|max:255',
                'prodi_id' => 'required|exists:prodi,id',
                'status' => 'required|in:belum,sudah',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            $mahasiswa = Mahasiswa::find($id);
            if (!$mahasiswa) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $mahasiswa->update($validator->validated());

            return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Data gagal diupdate'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $mahasiswa = Mahasiswa::find($id);
            if (!$mahasiswa) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }
            $mahasiswa->delete();
            return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Data gagal dihapus'], 500);
        }
    }

    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
            }

            $import = new MahasiswaImport();
            Excel::import($import, $request->file('file'));

            if ($import->getFailedCount() > 0) {
                Log::warning('Beberapa baris gagal diimpor pada Mahasiswa Import', [
                    'failed_count' => $import->getFailedCount(),
                    'errors' => $import->getErrors()
                ]);
            }

            $skippedSheets = $import->getSkippedSheets();
            $skippedMsg = count($skippedSheets) > 0 ? ", " . count($skippedSheets) . " sheet dilewati (" . implode(', ', $skippedSheets) . ")" : "";

            $skippedNames = $import->getSkippedNames();
            $skippedNamesMsg = count($skippedNames) > 0 ? ". Terdapat " . count($skippedNames) . " data yang dilewati karena ada kolom yang kosong: " . implode(' | ', $skippedNames) : "";

            return response()->json([
                'status' => true,
                'message' => "Import selesai. {$import->getSuccessCount()} berhasil, {$import->getFailedCount()} gagal{$skippedMsg}{$skippedNamesMsg}",
                'data' => [
                    'success' => $import->getSuccessCount(),
                    'failed' => $import->getFailedCount(),
                    'skipped' => count($skippedNames),
                    'skipped_sheets' => $skippedSheets,
                    'skipped_names' => $skippedNames,
                    'errors' => $import->getErrors(),
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('Gagal melakukan import Mahasiswa', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Import gagal: ' . $th->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $search = $request->query('search', '');

            // $user = $request->user();
            // $where = null;

            // if ($user && $user->prodi_id) {
            //     $where = ['prodi_id' => $user->prodi_id];
            // }

            // $data = \App\Services\Mahasiswa::all(
            //     null,   // offset
            //     20,     // limit
            //     $search,
            //     null,   // order
            //     null,   // dir
            //     $where, // where
            //     null    // pluck
            // );
            $user = $request->user();
            $where = null;
            if ($user && $user->prodi_id) {
                $prodi = $user->prodi->alias;
                // $prodi2 = strval($prodi);

                $where = [
                    ['mst_prodi.alias', '=', $prodi]
                ];
            }

            $data = \App\Services\Mahasiswa::all(
                null,   // offset
                20,     // limit
                $search,
                null,   // order
                null,   // dir
                $where, // where
                null    // pluck
            );

            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengambil data mahasiswa: ' . $th->getMessage()
            ], 500);
        }
    }
}
