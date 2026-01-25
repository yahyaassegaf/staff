<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HasilRapat;
use App\Models\LogSurat;
use App\Models\SuratIzinPenelitian;
use App\Models\SuratKeterangan;
use App\Models\SuratKeteranganAdministrasiKeuangan;
use App\Models\SuratKeteranganAktifMahasiswa;
use App\Models\SuratKeteranganLulusMataKuliah;
use App\Models\SuratKeteranganQismulAman;
use App\Models\SuratKeteranganTasmaKknPpl;
use App\Models\SuratKeteranganTransfer;
use App\Models\SuratKeteranganUjianKomprehensifDiniyah;
use App\Models\SuratPernyataanVerifikasiNilai;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $loginProdi = Auth::user()->prodi;
        $prodiId = $loginProdi ? $loginProdi->id : null;

        $logs = LogSurat::query()
            ->join('users', 'users.id', '=', 'log_surat.user_id')
            ->select(
                'log_surat.id',
                'log_surat.nomor',
                'log_surat.nomor_surat',
                'log_surat.nama_surat',
                'log_surat.user_id',
                'log_surat.created_at'
            )
            ->when($prodiId, fn($q) => $q->where('users.prodi_id', $prodiId));

        if ($request->filled('search')) {
            $search = $request->search;
            $logs->where(function ($q) use ($search) {
                $q->orWhere('log_surat.nomor', 'like', "%{$search}%")
                    ->orWhere('log_surat.nomor_surat', 'like', "%{$search}%")
                    ->orWhere('log_surat.nama_surat', 'like', "%{$search}%");
            });
        }

        $logs->orderBy(
            $request->input('sortBy', 'log_surat.id'),
            $request->input('sortType', 'desc')
        );

        $logs = $logs->paginate($request->input('limit', 10));

        return response()->json([
            'status' => true,
            'data' => $logs,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function cards(Request $request)
    {
        $loginProdi = Auth::user()->prodi;
        $prodiId = $loginProdi ? $loginProdi->id : null;

        $suratQueries = [
            SuratKeteranganLulusMataKuliah::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeteranganUjianKomprehensifDiniyah::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeteranganAdministrasiKeuangan::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeteranganTasmaKknPpl::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeteranganQismulAman::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeteranganAktifMahasiswa::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratPernyataanVerifikasiNilai::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeterangan::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratTugas::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratKeteranganTransfer::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            SuratIzinPenelitian::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
            HasilRapat::query()->when($prodiId, fn($q) => $q->where('prodi_id', $prodiId)),
        ];

        $totalSurat = 0;
        $pendingSurat = 0;
        $uploadedSurat = 0;

        foreach ($suratQueries as $query) {
            $totalSurat += (clone $query)->count();
            $pendingSurat += (clone $query)->where('status', 'pending')->count();
            $uploadedSurat += (clone $query)->where('status', 'uploaded')->count();
        }

        return response()->json([
            'status' => true,
            'data' => [
                'total' => $totalSurat,
                'pending' => $pendingSurat,
                'uploaded' => $uploadedSurat,
            ],
            'message' => 'Data berhasil diambil'
        ]);
    }
}
