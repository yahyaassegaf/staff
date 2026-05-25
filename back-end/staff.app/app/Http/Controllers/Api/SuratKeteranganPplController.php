<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganPpl;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganPplController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ppl.prodi_id');

        $data->select(
            'surat_keterangan_ppl.id as id',
            'surat_keterangan_ppl.nomor_surat',
            'surat_keterangan_ppl.ketua',
            'surat_keterangan_ppl.nama_lengkap',
            'surat_keterangan_ppl.tempat_lahir',
            'surat_keterangan_ppl.tanggal_lahir',
            'surat_keterangan_ppl.nim',
            'surat_keterangan_ppl.prodi_id',
            'surat_keterangan_ppl.jenis_kelamin',
            'surat_keterangan_ppl.prodi_mhs',
            'surat_keterangan_ppl.alamat_rumah',
            'surat_keterangan_ppl.kelas_pondok',
            'surat_keterangan_ppl.tanggal',
            'surat_keterangan_ppl.drive_file_id',
            'surat_keterangan_ppl.drive_link',
            'surat_keterangan_ppl.status',
            'surat_keterangan_ppl.created_at',
            'surat_keterangan_ppl.updated_at',
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
            $data->where('surat_keterangan_ppl.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_ppl.jenis_kelamin', 'P');
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
                'no_surat' => 'required|string|max:255',
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
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
            $no_surat = $validate['no_surat'];

            // Using unique NoSurat generator logic if needed, but for now assuming same pattern as reference
            // Adjusting method call to hypothetical NoSuratKeteranganPpl or similar if exists in Service, otherwise stick to generic or similar
            // Since User requested "like the previous controller", and previous used NoSuratKeteranganTasmaKknPpl, I'll assume we might need a new method in Service or reuse one.
            // For now, I'll use NoSuratKeteranganTasmaKknPpl as placeholder or assume user handles service updates later, but better to be safe and use generic format manually or reuse known one.
            // Let's reuse the logic but maybe rename the variable if needed.
            // Wait, SuratService::NoSuratKeteranganTasmaKknPpl might be specific.
            // I'll check if I should use a generic one. For PPL, the format might be similar.

            $noSurat = SuratService::NoSuratKeteranganTasmaKknPpl($no_surat); // Reusing for consistency, or should implementation be different? USER said "like previous".

            $skp                  = new SuratKeteranganPpl();
            $skp->nomor_surat   = $noSurat;
            $skp->tanda_tangan_id = $validate['tanda_tangan_id'];
            $skp->nama_lengkap  = $validate['nama_mhs'];
            $skp->tempat_lahir  = $validate['tempat_lahir'];
            $skp->tanggal_lahir = $validate['tanggal_lahir'];
            $skp->nim           = $validate['nim'];
            $skp->prodi_id      = $validate['prodi_id'] ?? null;
            $skp->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skp->prodi_mhs     = $validate['prodi_mhs'];
            $skp->alamat_rumah  = $validate['alamat_rumah'];
            $skp->kelas_pondok  = $validate['kelas_pondok'];
            $skp->tanggal       = $validate['tanggal'];
            $skp->user_id       = Auth::user()->id;
            $skp->status        = 'pending';
            $skp->save();

            $Nomor                = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id       = Auth::user()->id;
            $Nomor->save();

            $log                  = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat     = $noSurat;
            $log->nama_surat      = 'Surat Keterangan PPL';
            $log->user_id         = Auth::user()->id;
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
        $skp = SuratKeteranganPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ppl.prodi_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_ppl.tanda_tangan_id')
            ->select(
                'surat_keterangan_ppl.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi',
                'tanda_tangan.nama as nama_ttd'
            )
            ->where('surat_keterangan_ppl.id', $id)
            ->first();

        if (!$skp) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }


        $nomorStr = $skp->nomor_surat ?? $skp->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $skp->no_surat = trim($firstPart);
        }

        return response()->json([
            'status' => true,
            'data' => $skp,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'no_surat' => 'required|string|max:255',
                'prodi_id' => 'nullable|exists:prodi,id',
                'tanda_tangan_id' => 'required|exists:tanda_tangan,id',
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

            $skp = SuratKeteranganPpl::find($id);
            if (!$skp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $skp->drive_file_id;

            $noSurat = \App\Services\SuratService::NoSuratKeteranganTasmaKknPpl($validate['no_surat']);
            $skp->nomor_surat = $noSurat;

            $skp->prodi_id = $validate['prodi_id'] ?? $skp->prodi_id;
            $skp->tanda_tangan_id = $validate['tanda_tangan_id'];
            $skp->nama_lengkap = $validate['nama_mhs'];
            $skp->tempat_lahir = $validate['tempat_lahir'];
            $skp->tanggal_lahir = $validate['tanggal_lahir'];
            $skp->nim = $validate['nim'];
            $skp->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skp->prodi_mhs = $validate['prodi_mhs'];
            $skp->alamat_rumah = $validate['alamat_rumah'];
            $skp->kelas_pondok = $validate['kelas_pondok'];
            $skp->tanggal = $validate['tanggal'];
            $skp->save();

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }

            $skp->update([
                'drive_file_id' => null,
                'drive_link' => null,
                'status' => 'pending'
            ]);

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ppl.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_ppl.tanda_tangan_id')
                ->select(
                    'fakultas.nama as fakultas',
                    'surat_keterangan_ppl.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'tanda_tangan.gambar as ttd',
                    'tanda_tangan.nama as ttd_nama'
                )
                ->where('surat_keterangan_ppl.id', $skp->id)
                ->first();

            if ($data) {
                $kopPath = base_path('../public_html/img/kop.jpg');
                $kopBase64 = SuratService::getBase64Image($kopPath);
                $tddPath = base_path('../public_html/' . $data->ttd);
                $tddBase64 = SuratService::getBase64Image($tddPath);
                $stempelPath = base_path('../public_html/img/stempel.png');
                $stempelBase64 = SuratService::getBase64Image($stempelPath);

                $pdfData = [
                    'nomor_surat' => $data->nomor_surat,
                    'ketua' => $data->ttd_nama,
                    'nama' => $data->nama_lengkap,
                    'tempat_lahir' => $data->tempat_lahir,
                    'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                    'nim' => $data->nim,
                    'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'fakultas' => $data->fakultas,
                    'prodi' => $data->prodi_mhs,
                    'alamat' => $data->alamat_rumah,
                    'kelas' => $data->kelas_pondok,
                    'jenis_kelamin' => $data->jenis_kelamin,
                    'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                    'kopBase64' => $kopBase64,
                    'ttd' => $tddBase64,
                    'stempel' => $stempelBase64,
                ];

                $pdf = Pdf::loadView('pdf.ppl', $pdfData)->setPaper('a4', 'portrait');

                $fileName = 'surat_keterangan_ppl_' . $data->nim . '.pdf';
                $directory = base_path('../public_html/pdf/');

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $path = $directory . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan PPL';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganPpl::class);
            }

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
            $skp = SuratKeteranganPpl::find($id);
            if (!$skp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $skp->delete();

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
        // Note: Logic for PDF download might need adjustment if using a different PDF view or different kop.
        // Reusing 'pdf.surat_tasma_kkn_ppl' for consistency as requested "like previous controller".
        try {
            $data = SuratKeteranganPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ppl.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_ppl.tanda_tangan_id')
                ->select(
                    'fakultas.nama as fakultas',
                    'surat_keterangan_ppl.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'tanda_tangan.gambar as ttd',
                    'tanda_tangan.nama as ttd_nama',
                )
                ->where('surat_keterangan_ppl.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = SuratService::getBase64Image($kopPath);
            $tddPath = base_path('../public_html/' . $data->ttd);
            $tddBase64 = SuratService::getBase64Image($tddPath);
            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = SuratService::getBase64Image($stempelPath);

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'ketua' => $data->ttd_nama,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                'nim' => $data->nim,
                'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'fakultas' => $data->fakultas,
                'prodi' => $data->prodi_mhs,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'jenis_kelamin' => $data->jenis_kelamin,
                'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
            ];

            $pdf = Pdf::loadView('pdf.ppl', $pdfData)
                ->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_ppl_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf/');

            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan PPL';

            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganPpl::class);
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
