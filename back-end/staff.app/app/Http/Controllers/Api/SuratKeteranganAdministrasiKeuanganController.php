<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganAdministrasiKeuangan;
use App\Services\SuratService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class SuratKeteranganAdministrasiKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id');

        $data->select(
            'surat_keterangan_administrasi_keuangan.*',
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
                'prodi_id'      => 'nullable',
                'nama_mhs'      => 'required|string|max:255',
                'tempat_lahir'  => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim'           => 'required|string|max:255',
                'prodi_mhs'     => 'required|string|max:255',
                'alamat_rumah'  => 'required|string',
                'kelas_pondok'  => 'required|string|max:255',
                'tanggal'       => 'required|date',
                'kepala_biro'   => 'nullable|string',
            ]);

            $loginArgs = Auth::user()->prodi ? Auth::user()->prodi->alias : 'UNIV';
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($loginArgs);

            $noSurat = SuratService::NoSuratKeteranganLulusMataKuliah($no_surat, $unit);

            $data = new SuratKeteranganAdministrasiKeuangan();
            $data->nomor_surat  = $noSurat;
            $data->prodi_id     = $request->prodi_id;
            $data->nama_lengkap = $request->nama_mhs;
            $data->tempat_lahir = $request->tempat_lahir;
            $data->tanggal_lahir = $request->tanggal_lahir;
            $data->nim          = $request->nim;
            $data->prodi_mhs    = $request->prodi_mhs;
            $data->alamat_rumah = $request->alamat_rumah;
            $data->kelas_pondok = $request->kelas_pondok;
            $data->tanggal      = $request->tanggal;
            $data->kepala_biro  = $request->kepala_biro;
            $data->user_id      = Auth::user()->id;
            $data->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor      = $no_surat;
            $Nomor->user_id    = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat = 'Surat Keterangan Administrasi Keuangan';
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
        $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
            ->select(
                'surat_keterangan_administrasi_keuangan.*',
                'prodi.nama as nama_prodi'
            )
            ->where('surat_keterangan_administrasi_keuangan.id', $id)
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
                'kepala_biro' => 'nullable|string',
            ]);

            $data = SuratKeteranganAdministrasiKeuangan::find($id);
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
            $data = SuratKeteranganAdministrasiKeuangan::find($id);
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
            $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->select(
                    'surat_keterangan_administrasi_keuangan.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'fakultas.nama as fakultas'
                )
                ->where('surat_keterangan_administrasi_keuangan.id', $id)
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
                'fakultas' => $data->fakultas ?? '-',
                'prodi' => $data->nama_prodi ?? '-',
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_penandatangan' => $data->kepala_biro,
                'jabatan_penandatangan' => 'Kepala Biro',
                'kopBase64' => $kopBase64,
            ];

            $pdf = Pdf::loadView('pdf.administrasi_keuangan', $pdfData)
                ->setPaper('a4', 'portrait');
            Log::info('pdf');
            $fileName = 'surat_keterangan_administrasi_keuangan_' . $data->nim . '.pdf';

            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Administrasi Keuangan';
            UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganAdministrasiKeuangan::class);

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
