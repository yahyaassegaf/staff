<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganSpm;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganSpmController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganSpm::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_spm.prodi_id');

        $data->select(
            'surat_keterangan_spm.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan_spm.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan_spm.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('tempat_tugas', 'like', "%{$search}%")
                    ->orWhere('tahun', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_spm.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_spm.jenis_kelamin', 'P');
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
                'prodi_id' => 'required|exists:prodi,id',
                'no_surat' => 'required|string|max:255',
                'nama_lengkap' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'prodi_mhs' => 'required|string|max:255',
                'alamat' => 'required|string',
                'nama_ortu' => 'required|string|max:255',
                'tempat_tugas' => 'required|string|max:255',
                'alamat_tugas' => 'required|string',
                'tahun' => 'required|string|max:100',
                'semester' => 'required|string|max:50',
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

            $no_surat = $validate['no_surat'];

            $noSurat = SuratService::formatNomorSurat('STSPM', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $spm = new SuratKeteranganSpm();
            $spm->nomor_surat = $noSurat;
            $spm->prodi_id = $validate['prodi_id'];
            $spm->nama_lengkap = $validate['nama_lengkap'];
            $spm->nim = $validate['nim'];
            $spm->tempat_lahir = $validate['tempat_lahir'];
            $spm->tanggal_lahir = $validate['tanggal_lahir'];
            $spm->prodi_mhs = $validate['prodi_mhs'];
            $spm->alamat = $validate['alamat'];
            $spm->nama_ortu = $validate['nama_ortu'];
            $spm->tempat_tugas = $validate['tempat_tugas'];
            $spm->alamat_tugas = $validate['alamat_tugas'];
            $spm->tahun = $validate['tahun'];
            $spm->semester = $validate['semester'];
            $spm->tanggal = $validate['tanggal'];
            $spm->user_id = Auth::user()->id;
            $spm->jenis_kelamin = Auth::user()->jenis_kelamin;
            $spm->status = 'pending';
            $spm->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            $log                = new LogSurat();
            $log->nomor = $no_surat;
            $log->nomor_surat   = $noSurat;
            $log->nama_surat    = 'Surat Keterangan SPM';
            $log->user_id       = Auth::user()->id;
            $log->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::error($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }

    public function show($id)
    {
        $spm = SuratKeteranganSpm::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_spm.prodi_id')
            ->select(
                'surat_keterangan_spm.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_spm.id', $id)
            ->first();

        if (!$spm) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        
        $nomorStr = $spm->nomor_surat ?? $spm->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $spm->no_surat = trim($firstPart);
        }

        return response()->json([
            'status' => true,
            'data' => $spm,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'prodi_id' => 'required|exists:prodi,id',
                'no_surat' => 'required|string|max:255',
                'nama_lengkap' => 'required|string|max:255',
                'nim' => 'required|string|max:255',
                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'prodi_mhs' => 'required|string|max:255',
                'alamat' => 'required|string',
                'nama_ortu' => 'required|string|max:255',
                'tempat_tugas' => 'required|string|max:255',
                'alamat_tugas' => 'required|string',
                'tahun' => 'required|string|max:100',
                'semester' => 'required|string|max:50',
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

            $spm = SuratKeteranganSpm::find($id);
            if (!$spm) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $spm->drive_file_id;

            $noSurat = SuratService::formatNomorSurat('STSPM', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id']);

            $spm->fill([
                'nomor_surat' => $noSurat,
                'prodi_id' => $validate['prodi_id'],
                'nama_lengkap' => $validate['nama_lengkap'],
                'nim' => $validate['nim'],
                'tempat_lahir' => $validate['tempat_lahir'],
                'tanggal_lahir' => $validate['tanggal_lahir'],
                'prodi_mhs' => $validate['prodi_mhs'],
                'alamat' => $validate['alamat'],
                'nama_ortu' => $validate['nama_ortu'],
                'tempat_tugas' => $validate['tempat_tugas'],
                'alamat_tugas' => $validate['alamat_tugas'],
                'tahun' => $validate['tahun'],
                'semester' => $validate['semester'],
                'tanggal' => $validate['tanggal'],
                'jenis_kelamin' => Auth::user()->jenis_kelamin,
                'user_id' => Auth::user()->id,
            ]);
            $spm->save();

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }

            $spm->update([
                'drive_file_id' => null,
                'drive_link' => null,
                'status' => 'pending'
            ]);

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganSpm::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_spm.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_spm.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'tanda_tangan.gambar as ttd'
                )
                ->where('surat_keterangan_spm.id', $spm->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.surat_keterangan_spm', $pdfData)->setPaper('a4', 'portrait');

                $fileName = 'surat_keterangan_spm_' . $data->nim . '.pdf';
                $directory = base_path('../public_html/pdf');

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan SPM';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganSpm::class);
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
            $spm = SuratKeteranganSpm::find($id);
            if (!$spm) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $spm->delete();

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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        try {
            $data = SuratKeteranganSpm::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_spm.prodi_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
                ->select(
                    'surat_keterangan_spm.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'prodi.nama_kepala as nama_kepala_prodi',
                    'prodi.nidn_kepala as nidn_kepala_prodi',
                    'tanda_tangan.gambar as ttd'
                )
                ->where('surat_keterangan_spm.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $pdfData = $this->buildPdfData($data);

            $pdf = Pdf::loadView('pdf.surat_keterangan_spm', $pdfData)->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_spm_' . $data->nim . '.pdf';
            $directory = base_path('../public_html/pdf');

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan SPM';
            $googlePath = $data->nama_prodi . '/' . $nameTable . '/' . $fileName;

            if (!Storage::disk('google')->exists($googlePath)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->nama_prodi, SuratKeteranganSpm::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error((string) $th);
            return response()->json([
                'status' => false,
                'message' => 'Gagal mengunduh PDF: Terjadi kesalahan pada server'
            ], 500);
        }
    }

    private function buildPdfData($data)
    {
        $stempelPath = base_path('../public_html/img/stempel.png');
        $stempelBase64 = SuratService::getBase64Image($stempelPath);

        $tddPath = base_path('../public_html/' . $data->ttd);
        $tddBase64 = SuratService::getBase64Image($tddPath);

        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = SuratService::getBase64Image($kopPath);

        // Cari tanda tangan Pengawas SPM dari tabel setting_jabatan dengan key pengawas_spm
        $settingJabatan = SettingJabatan::with('tandaTangan')
            ->where('kunci_jabatan', 'pengawas_spm')
            ->first();

        $pengawasNama = 'Dr. Muhamad Sholehuddin, M.H.I.';
        $pengawasTtdBase64 = null;

        if ($settingJabatan && $settingJabatan->tandaTangan) {
            $pengawasNama = $settingJabatan->tandaTangan->nama;
            if (!empty($settingJabatan->tandaTangan->gambar)) {
                $pengawasPath = base_path('../public_html/' . $settingJabatan->tandaTangan->gambar);
                $pengawasTtdBase64 = SuratService::getBase64Image($pengawasPath);
            }
        }

        return [
            'nomor_surat' => $data->nomor_surat,
            'nama' => $data->nama_lengkap,
            'nim' => $data->nim,
            'tempat_lahir' => $data->tempat_lahir,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
            'prodi_mhs' => $data->prodi_mhs,
            'alamat' => $data->alamat,
            'nama_ortu' => $data->nama_ortu,
            'tempat_tugas' => $data->tempat_tugas,
            'alamat_tugas' => $data->alamat_tugas,
            'tahun' => $data->tahun,
            'semester' => $data->semester,
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'nama_prodi' => $data->nama_prodi,
            'alias_prodi' => $data->alias_prodi,
            'nama_kepala_prodi' => $data->nama_kepala_prodi,
            'nidn_kepala_prodi' => $data->nidn_kepala_prodi,
            'kopBase64' => $kopBase64,
            'stempel' => $stempelBase64,
            'ttd' => $tddBase64,
            'pengawas_nama' => $pengawasNama,
            'pengawas_ttd' => $pengawasTtdBase64
        ];
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
