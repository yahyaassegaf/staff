<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganTasmaKknPpl;
use App\Models\TandaTangan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganTasmaKknPplController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganTasmaKknPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_tasma_kkn_ppl.prodi_id');

        $data->select(
            'surat_keterangan_tasma_kkn_ppl.id as id',
            'surat_keterangan_tasma_kkn_ppl.nomor_surat',
            'surat_keterangan_tasma_kkn_ppl.ketua',
            'surat_keterangan_tasma_kkn_ppl.nama_lengkap',
            'surat_keterangan_tasma_kkn_ppl.tempat_lahir',
            'surat_keterangan_tasma_kkn_ppl.tanggal_lahir',
            'surat_keterangan_tasma_kkn_ppl.nim',
            'surat_keterangan_tasma_kkn_ppl.prodi_id',
            'surat_keterangan_tasma_kkn_ppl.jenis_kelamin',
            'surat_keterangan_tasma_kkn_ppl.prodi_mhs',
            'surat_keterangan_tasma_kkn_ppl.alamat_rumah',
            'surat_keterangan_tasma_kkn_ppl.kelas_pondok',
            'surat_keterangan_tasma_kkn_ppl.tanggal',
            'surat_keterangan_tasma_kkn_ppl.drive_file_id',
            'surat_keterangan_tasma_kkn_ppl.status',
            'surat_keterangan_tasma_kkn_ppl.created_at',
            'surat_keterangan_tasma_kkn_ppl.updated_at',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('prodi_mhs', 'like', "%{$search}%")
                    ->orWhere('kelas_pondok', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_tasma_kkn_ppl.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_tasma_kkn_ppl.jenis_kelamin', 'P');
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'desc')
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
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'nullable|exists:prodi,id',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
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
            Log::info($validate);

            $login = $validate['prodi_mhs'];
            $no = NoSurat::orderByDesc('id')->value('nomor') ?? 0;
            $no_surat = str_pad($no + 1, 3, '0', STR_PAD_LEFT);

            $unit = 'K.' . strtoupper($login);
            // Handling if login is generic or derived logic needs adjustment, sticking to reference pattern

            $noSurat = SuratService::NoSuratKeteranganTasmaKknPpl($no_surat);

            $sktkp                = new SuratKeteranganTasmaKknPpl();
            $sktkp->nomor_surat   = $noSurat;
            $sktkp->tanda_tangan_id = $validate['tanda_tangan_id'] ?? null;
            // Set ketua from tanda_tangan nama if provided
            if (!empty($validate['tanda_tangan_id'])) {
                $tandaTangan = TandaTangan::find($validate['tanda_tangan_id']);
                $sktkp->ketua = $tandaTangan?->nama;
            }
            $sktkp->nama_lengkap  = $validate['nama_mhs'];
            $sktkp->tempat_lahir  = $validate['tempat_lahir'];
            $sktkp->tanggal_lahir = $validate['tanggal_lahir'];
            $sktkp->nim           = $validate['nim'];
            $sktkp->prodi_id      = $validate['prodi_id'] ?? null;
            $sktkp->jenis_kelamin = Auth::user()->jenis_kelamin;
            $sktkp->prodi_mhs     = $validate['prodi_mhs'];
            $sktkp->alamat_rumah  = $validate['alamat_rumah'];
            $sktkp->kelas_pondok  = $validate['kelas_pondok'];
            $sktkp->tanggal       = $validate['tanggal'];
            $sktkp->user_id       = Auth::user()->id;
            $sktkp->status        = 'pending';
            $sktkp->save();

            $Nomor                = new NoSurat();
            $Nomor->nomor         = $no_surat;
            $Nomor->user_id       = Auth::user()->id;
            $Nomor->save();

            $log                  = new LogSurat();
            $log->nomor         = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan TASMA, KKN, PPL';
            $log->user_id       = Auth::user()->id;
            $log->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }

    public function show($id)
    {
        $sktkp = SuratKeteranganTasmaKknPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_tasma_kkn_ppl.prodi_id')
            ->select(
                'surat_keterangan_tasma_kkn_ppl.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_tasma_kkn_ppl.id', $id)
            ->first();

        if (!$sktkp) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $sktkp,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'nullable|exists:prodi,id',
                'tanda_tangan_id' => 'nullable|exists:tanda_tangan,id',
                'nama_mhs' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'nim' => 'required|string|max:255',
                'prodi_mhs' => 'required|string|max:255',
                'alamat_rumah' => 'required|string',
                'kelas_pondok' => 'required|string|max:255',
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

            $sktkp = SuratKeteranganTasmaKknPpl::find($id);
            if (!$sktkp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $sktkp->prodi_id = $validate['prodi_id'] ?? $sktkp->prodi_id;
            if (array_key_exists('tanda_tangan_id', $validate)) {
                $sktkp->tanda_tangan_id = $validate['tanda_tangan_id'];
                if (!empty($validate['tanda_tangan_id'])) {
                    $tandaTangan = TandaTangan::find($validate['tanda_tangan_id']);
                    $sktkp->ketua = $tandaTangan?->nama;
                }
            }
            $sktkp->nama_lengkap = $validate['nama_mhs'];
            $sktkp->tempat_lahir = $validate['tempat_lahir'];
            $sktkp->tanggal_lahir = $validate['tanggal_lahir'];
            $sktkp->nim = $validate['nim'];
            $sktkp->jenis_kelamin = Auth::user()->jenis_kelamin;
            $sktkp->prodi_mhs = $validate['prodi_mhs'];
            $sktkp->alamat_rumah = $validate['alamat_rumah'];
            $sktkp->kelas_pondok = $validate['kelas_pondok'];
            $sktkp->tanggal = $validate['tanggal'];
            $sktkp->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $sktkp = SuratKeteranganTasmaKknPpl::find($id);
            if (!$sktkp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $sktkp->delete();

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
            $data = SuratKeteranganTasmaKknPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_tasma_kkn_ppl.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_tasma_kkn_ppl.tanda_tangan_id')
                ->select(
                    'fakultas.nama as fakultas',
                    'surat_keterangan_tasma_kkn_ppl.*',
                    'prodi.nama as prodi',
                    'prodi.alias as alias_prodi',
                    'tanda_tangan.nama as nama_ttd',
                    'tanda_tangan.gambar as ttd',
                )
                ->where('surat_keterangan_tasma_kkn_ppl.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            // Assuming img exists; use placeholder or catch error if needed.

            $kopBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($kopPath));

            $tddPath = base_path('../public_html/' . $data->ttd);

            $tddBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($tddPath));
            $stempelPath = base_path('../public_html/img/stempel.png');

            $stempelBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($stempelPath));

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'ketua' => $data->nama_ttd ?? $data->ketua,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => Carbon::parse($data->tanggal_lahir)->translatedFormat('d F Y'),
                'nim' => $data->nim,
                'tanggal' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'fakultas' => $data->fakultas,
                'prodi' => $data->prodi_mahasiswa, // Using prodi_mhs field as per migration text
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'jenis_kelamin' => $data->jenis_kelamin,
                'tanggal_surat' => Carbon::parse($data->tanggal)->translatedFormat('d F Y'),
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
            ];

            // Use the view found in user's open documents
            $pdf = Pdf::loadView('pdf.surat_tasma_kkn_ppl', $pdfData)
                ->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_tasma_kkn_ppl_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan TASMA, KKN, PPL';

            $googlePath = $data->prodi . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi, SuratKeteranganTasmaKknPpl::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diunduh: ' . $th->getMessage()
            ]);
        }
    }

    public function getProdi()
    {
        try {
            $login = Auth::user()->prodi;
            if ($login) {
                $prodi = Prodi::where('id', $login->id)->first();
            } else {
                $prodi = Prodi::all();
            }
            return response()->json([
                'status' => true,
                'data' => $prodi
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
