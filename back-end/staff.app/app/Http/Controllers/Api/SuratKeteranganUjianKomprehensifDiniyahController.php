<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganUjianKomprehensifDiniyah;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratKeteranganUjianKomprehensifDiniyahController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganUjianKomprehensifDiniyah::join('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id');

        $data->select(
            'surat_keterangan_ujian_komprehensif_diniyah.*',
            'prodi.nama as nama_prodi'
        );

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%")
                    ->orWhere('kelas_pondok', 'like', "%{$search}%");
            });
        }

        if ($request->filled('prodi_id')) {
            $data->where('prodi_id', $request->prodi_id);
        }

        $login  = Auth::user()->prodi;
        if ($login) {
            $data->where('prodi_id', $login->id);
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'asc')
        );

        $data = $data->paginate($request->input('limit', 10));

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
        Log::info($request->all());
        try {
            $validate = $request->validate([
                'prodi_id'      => 'required',
                'nama_mhs'      => 'required|string|max:255',
                'tempat_lahir'  => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim'           => 'required|string|max:255',
                'prodi_mhs'     => 'required|string|max:255',
                'alamat_rumah'  => 'required|string',
                'kelas_pondok'  => 'required|string|max:255',
                'tanggal'       => 'required|date',
                'koor_kompre'   => 'nullable|string',
                'ttd'           => 'nullable|string',
            ]);

            $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            // Format nomor surat: 001/SK.UKD/K.FTI/2025
            // K + Alias Prodi (KFTI)
            $tahun = date('Y');
            $unit = 'K.' . strtoupper($login);
            // Assuming strict format is needed, adapting from existing service pattern would be best, 
            // but for now constructing manually as per common pattern or using the provided number directly if handled by service.
            // Logic in reference uses SuratService::NoSuratKeteranganLulusMataKuliah($no_surat, $unit);
            // I will strictly follow the pattern: Nomor/KodeSurat/Unit/Tahun
            // User requested 'nomor_surat' attribute. 
            // In Store method of reference: $noSurat = SuratService::NoSuratKeteranganLulusMataKuliah($no_surat, $unit);
            // I will use a generic format or custom if Service not updated.
            // Let's assume standard format: NO/SK.UKD/UNIT/YEAR

            $noSurat = SuratService::NoSuratKeteranganLulusMataKuliah($no_surat, $unit);

            $data = new SuratKeteranganUjianKomprehensifDiniyah();
            $data->nomor_surat = $noSurat;
            $data->prodi_id = $request->prodi_id;
            $data->nama_lengkap = $request->nama_mhs;
            $data->tempat_lahir = $request->tempat_lahir;
            $data->tanggal_lahir = $request->tanggal_lahir;
            $data->nim = $request->nim;
            $data->prodi_mhs = $request->prodi_mhs;
            $data->alamat_rumah = $request->alamat_rumah;
            $data->kelas_pondok = $request->kelas_pondok;
            $data->tanggal = $request->tanggal;
            $data->koor_komprehensif = $request->koordinator_kompre;
            $data->ttd = $request->ttd;
            $data->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat = 'Surat Keterangan Ujian Komprehensif Diniyah';
            $log->user_id = Auth::user()->id;
            $log->save();

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
        $data = SuratKeteranganUjianKomprehensifDiniyah::join('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id')
            ->select(
                'surat_keterangan_ujian_komprehensif_diniyah.*',
                'prodi.nama as nama_prodi'
            )
            ->where('surat_keterangan_ujian_komprehensif_diniyah.id', $id)
            ->first();

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

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validate = $request->validate([
                'prodi_id' => 'sometimes|exists:prodi,id',
                'nama_lengkap' => 'sometimes|string|max:255',
                'tempat_lahir' => 'sometimes|string|max:100',
                'tanggal_lahir' => 'sometimes|date',
                'nim' => 'sometimes|string|max:255',
                'prodi_mhs' => 'sometimes|string|max:255',
                'alamat_rumah' => 'sometimes|string',
                'kelas_pondok' => 'sometimes|string|max:255',
                'tanggal' => 'sometimes|date',
                'koor_komprehensif' => 'nullable|string',
                'ttd' => 'nullable|string',
            ]);

            $data = SuratKeteranganUjianKomprehensifDiniyah::find($id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $data->update($validate);

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
            $data = SuratKeteranganUjianKomprehensifDiniyah::find($id);
            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $data->delete();

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

    public function downloadPdf($id)
    {
        Log::info('masuk.....');
        Log::info($id);
        try {
            $data = SuratKeteranganUjianKomprehensifDiniyah::join('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id')
                ->join('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_keterangan_ujian_komprehensif_diniyah.*',
                    'prodi.nama as nama_prodi',
                    'fakultas.nama as fakultas'
                )
                ->where('surat_keterangan_ujian_komprehensif_diniyah.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            Log::info($data);
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));
            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'fakultas' => $data->fakultas,
                'prodi' => $data->nama_prodi,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_penandatangan' => $data->koor_komprehensif, // Assuming ttd field contains the name
                'jabatan_penandatangan' => 'Ketua / Koordinator Komprehensip',
                'kopBase64' => $kopBase64, // Static title based on blade template
            ];

            // Load view pdf.komprehensif created by user
            $pdf = Pdf::loadView('pdf.komprehensif', $pdfData)
                ->setPaper('a4', 'portrait');
            Log::info('pdf');
            $fileName = 'surat_keterangan_ukd_' . $data->nim . '.pdf';

            $fileName = 'surat_keterangan_ujian_komprehensif_diniyah_' . $data->nim . '_' . $data->alias_prodi . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Ujian Komprehensif Diniyah';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganUjianKomprehensifDiniyah::class);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diunduh'
            ]);
        }
    }
}
