<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\FakultasProdi;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganTransfer;

use App\Models\ThAkademik;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganTransferController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganTransfer::join('prodi', 'prodi.id', '=', 'surat_keterangan_transfer.prodi_id');

        $data->select(
            'surat_keterangan_transfer.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan_transfer.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan_transfer.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('surat_keterangan_transfer.nim', 'like', "%{$search}%")
                    ->orWhere('surat_keterangan_transfer.nama', 'like', "%{$search}%")
                    ->orWhere('prodi.nama', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_transfer.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_transfer.jenis_kelamin', 'P');
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'desc')
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
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'th_akademik_id' => 'nullable|exists:th_akademik,id',
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jurusan_prodi' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'tanggal' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            // $login = Auth::user()->prodi->alias;
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $fakultas = FakultasProdi::join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')->where('prodi_id', $validate['prodi_id'])
                ->first();
            $besar = ucwords($fakultas->nama);
            $nama_fakultas = 'Fakultas ' . $besar;

            $inisial = collect(explode(' ', $nama_fakultas))
                ->map(fn($kata) => strtoupper(substr($kata, 0, 1)))
                ->take(2)
                ->implode('');
            $formattedNoSurat = SuratService::NoSuratKeteranganTransfer($no_surat, $inisial);

            $skt = new SuratKeteranganTransfer();
            $skt->nomor = $formattedNoSurat;
            $skt->nama = $validate['nama'];
            $skt->tanggal_lahir = $validate['tanggal_lahir'];
            $skt->nim = $validate['nim'];
            $skt->jurusan_prodi = $validate['jurusan_prodi'];
            $skt->semester = $validate['semester'];
            $skt->tahun_akademik = $validate['th_akademik_id'] ?? null;
            // Set tahun_akademik from th_akademik if provided
            // if (!empty($validate['th_akademik_id'])) {
            //     $thAkademik = ThAkademik::find($validate['th_akademik']);
            //     $skt->tahun_akademik = $thAkademik ? $thAkademik->nama . ' ' . $thAkademik->semester : $validate['tahun_akademik'];
            // } else {
            //     $skt->tahun_akademik = $validate['tahun_akademik'] ?? '';
            // }
            $skt->tanggal = $validate['tanggal'];
            $skt->user_id = Auth::user()->id;
            $skt->prodi_id = $validate['prodi_id'];
            $skt->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skt->status = 'pending';
            $skt->save();

            $Nomor = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id = Auth::user()->id;
            $Nomor->save();

            $log = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat = $formattedNoSurat;
            $log->nama_surat = 'Surat Keterangan Transfer';
            $log->user_id = Auth::user()->id;
            $log->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $data = SuratKeteranganTransfer::join('prodi', 'prodi.id', '=', 'surat_keterangan_transfer.prodi_id')
            ->select('surat_keterangan_transfer.*', 'prodi.nama as nama_prodi')
            ->where('surat_keterangan_transfer.id', $id)
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
        try {

            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required',
                'th_akademik_id' => 'nullable|exists:th_akademik,id',
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'jurusan_prodi' => 'required|string|max:255',
                'semester' => 'required|string|max:255',
                'tahun_akademik' => 'nullable|string|max:255',
                'tanggal' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $skt = SuratKeteranganTransfer::find($id);
            if (!$skt) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }


            $skt->nama = $validate['nama'];
            $skt->tanggal_lahir = $validate['tanggal_lahir'];
            $skt->nim = $validate['nim'];
            $skt->jurusan_prodi = $validate['jurusan_prodi'];
            $skt->semester = $validate['semester'];
            $skt->th_akademik_id = $validate['th_akademik_id'] ?? $skt->th_akademik_id;
            // Set tahun_akademik from th_akademik if provided
            if (!empty($validate['th_akademik_id'])) {
                $thAkademik = ThAkademik::find($validate['th_akademik_id']);
                $skt->tahun_akademik = $thAkademik ? $thAkademik->nama . ' ' . $thAkademik->semester : $validate['tahun_akademik'];
            } elseif (!empty($validate['tahun_akademik'])) {
                $skt->tahun_akademik = $validate['tahun_akademik'];
            }
            $skt->tanggal = $validate['tanggal'];
            $skt->prodi_id = $validate['prodi_id'];
            $skt->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skt->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $skt = SuratKeteranganTransfer::find($id);
            if (!$skt) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            $skt->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }

    public function downloadPdf($id)
    {
        try {
            $data = SuratKeteranganTransfer::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_transfer.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->leftJoin('th_akademik', 'th_akademik.id', '=', 'surat_keterangan_transfer.tahun_akademik')
                ->select(
                    'surat_keterangan_transfer.*',
                    'prodi.nama as prodi_name',
                    'prodi.nama_kepala',
                    'prodi.nidn_kepala',
                    'fakultas.nama as fakultas_name',
                    'fakultas.dekan as dekan',
                    'tanda_tangan.gambar as ttd',
                    'th_akademik.nama as th_akademik_nama',
                    'th_akademik.semester as th_akademik_semester',
                    'fakultas.dekan as dekan'
                )
                ->where('surat_keterangan_transfer.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));

            $ttdPath = base_path('../public_html/' . $data->ttd);
            $ttdBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($ttdPath));

            // Build tahun_akademik from th_akademik or fallback to stored value
            $tahunAkademik = $data->th_akademik_nama;
            // if ($data->th_akademik_nama) {
            //     $tahunAkademik = $data->th_akademik_nama . ' ' . $data->th_akademik_semester;
            // }

            $fakultas = ucwords($data->fakultas_name);
            $nama_fakultas = 'Fakultas ' . $fakultas;

            $pdfData = [
                'nomor' => $data->nomor,
                'dekan' => $data->nama_ttd ?? $data->dekan,
                'nama' => $data->nama,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'jurusan_prodi' => $data->jurusan_prodi,
                'semester' => $data->semester,
                'tahun_akademik' => $tahunAkademik,
                'nama_fakultas' => $nama_fakultas,
                'dekan' => $data->dekan,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'nama_kepala' => $data->nama_ttd ?? $data->nama_kepala,
                'nidn_kepala' => $data->nidn_kepala,
                'kopBase64' => $kopBase64,
                'ttd' => $ttdBase64,
                'stempel' => $stempelBase64
            ];

            $pdf = Pdf::loadView('pdf.surat_keterangan_transfer', $pdfData)->setPaper('a4', 'portrait');
            $fileName = 'surat_keterangan_transfer_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Transfer';

            $googlePath = $data->prodi_name . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratKeteranganTransfer::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal generate PDF']);
        }
    }

    public function getProdi()
    {
        try {
            $login = Auth::user()->prodi;
            if ($login) {
                $prodi = Prodi::where('id', $login->id)->get();
            } else {
                $prodi = Prodi::all();
            }
            return response()->json(['status' => true, 'data' => $prodi]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Gagal ambil data prodi']);
        }
    }

    public function getThAkademik()
    {
        try {
            $thAkademik = ThAkademik::orderBy('kode', 'desc')->get();
            return response()->json(['status' => true, 'data' => $thAkademik]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Gagal ambil data tahun akademik']);
        }
    }
}
