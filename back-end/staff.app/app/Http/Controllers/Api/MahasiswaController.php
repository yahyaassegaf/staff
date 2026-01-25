<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Mahasiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {

        $data = Mahasiswa::table($request);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(Mahasiswa::find($id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $nim
     * @return \Illuminate\Http\Response
     */
    public function nim(Request $request)
    {
        $nim = $request->nim;
        $whereIn = $request->whereIn;
        return response()->json(Mahasiswa::nim($nim, $whereIn), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $search
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, $search = null)
    {
        // Ambil keyword dari segment URL atau query parameter 'search'
        $keyword = $search ?? $request->query('search');
        $keyword = trim($keyword ?? '');

        $where = null;
        // Hanya filter prodi jika user memiliki prodi_id
        if (Auth::user() && Auth::user()->prodi_id) {
            $where = [
                ['mst_mhs.prodi_id', '=', Auth::user()->prodi_id]
            ];
        }

        Log::info("Mahasiswa Search - Keyword: '{$keyword}', User Prodi: " . (Auth::user()->prodi_id ?? 'None'));

        $data = Mahasiswa::all(null, 30, $keyword, 'mst_mhs.nim', 'asc', $where);
        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $search
     * @return \Illuminate\Http\Response
     */
    public function getSemester(Request $request)
    {
        $data = Mahasiswa::getSemester($request->th_akademik_id, $request->prodi_id, $request->jk_id);
        return response()->json($data);
    }

    public function updateStatusMahasiswa(Request $request)
    {
        $data = Mahasiswa::updateStatusMahasiswa($request->nim, $request->status_id);
        return response()->json($data);
    }

    public function cekPelanggaran($nim)
    {
        $data = Mahasiswa::cekPelanggaran($nim);
        return response()->json($data);
    }
}
