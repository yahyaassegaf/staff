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
                'no_surat' => 'required|string|unique:nomor,nomor',
                'tahun_akademik' => 'nullable|string|max:255',
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'tempat_lahir' => 'nullable|string|max:100',
                'nim' => 'required|string|max:255',
                'jurusan_prodi' => 'required|string|max:255',
                'semester' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'universitas_tujuan' => 'nullable|string|max:255',
                'tanggal' => 'required|date',
            ], [
                'no_surat.unique' => 'Nomor surat sudah terpakai',
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
            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('SKM', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $skt = new SuratKeteranganTransfer();
            $skt->nomor = $formattedNoSurat;
            $skt->nama = $validate['nama'];
            $skt->tanggal_lahir = $validate['tanggal_lahir'];
            $skt->tempat_lahir = $validate['tempat_lahir'] ?? null;
            $skt->nim = $validate['nim'];
            $skt->jurusan_prodi = $validate['jurusan_prodi'];

            $semesterVal = $validate['semester'] ?? null;
            if (empty($semesterVal)) {
                try {
                    $mhsApi = \App\Services\Mahasiswa::nim($validate['nim']);
                    if ($mhsApi && isset($mhsApi->semester)) {
                        $semesterVal = self::formatSemester($mhsApi->semester);
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to fetch student semester in store: " . $e->getMessage());
                }
            }
            $skt->semester = $semesterVal ?? 'IX (Sembilan)';

            $skt->tahun_akademik = $validate['tahun_akademik'] ?? null;
            $skt->alamat = $validate['alamat'] ?? null;
            $skt->universitas_tujuan = $validate['universitas_tujuan'] ?? null;
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

            // Re-fetch record with joins to build the new PDF
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
                    'fakultas.nidn_dekan as nidn_dekan',
                    'tanda_tangan.gambar as ttd',
                    'th_akademik.nama as th_akademik_nama',
                    'th_akademik.semester as th_akademik_semester'
                )
                ->where('surat_keterangan_transfer.id', $skt->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.surat_keterangan_transfer', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganTransferController');
                $fileName = 'surat_keterangan_transfer_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Transfer';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratKeteranganTransfer::class);
            }

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


        $nomorStr = $data->nomor_surat ?? $data->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $data->no_surat = trim($firstPart);
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
                'no_surat' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($id) {
                        $skt = \App\Models\SuratKeteranganTransfer::find($id);
                        if ($skt) {
                            $originalNoSurat = '';
                            $nomorStr = $skt->nomor_surat ?? $skt->nomor ?? null;
                            if ($nomorStr) {
                                $parts = explode('/', $nomorStr);
                                $firstPart = $parts[0];
                                if (strpos($firstPart, '-') !== false) {
                                    $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
                                }
                                $originalNoSurat = trim($firstPart);
                            }
                            if ($value !== $originalNoSurat) {
                                if (\App\Models\NoSurat::where('nomor', $value)->exists()) {
                                    $fail('Nomor surat sudah terpakai.');
                                }
                            }
                        }
                    }
                ],
                'prodi_id' => 'required',
                'nama' => 'required|string|max:255',
                'tanggal_lahir' => 'required|date',
                'tempat_lahir' => 'nullable|string|max:100',
                'nim' => 'required|string|max:255',
                'jurusan_prodi' => 'required|string|max:255',
                'semester' => 'nullable|string|max:255',
                'tahun_akademik' => 'nullable|string|max:255',
                'alamat' => 'nullable|string',
                'universitas_tujuan' => 'nullable|string|max:255',
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

            $oldDriveFileId = $skt->drive_file_id;
            $oldLocalPath = $skt->local_path;

            $skt->nama = $validate['nama'];
            $skt->tanggal_lahir = $validate['tanggal_lahir'];
            $skt->tempat_lahir = $validate['tempat_lahir'] ?? $skt->tempat_lahir;
            $skt->nim = $validate['nim'];
            $skt->jurusan_prodi = $validate['jurusan_prodi'];

            $formattedNoSurat = \App\Services\SuratService::formatNomorSurat('SKM', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id']);
            $skt->nomor = $formattedNoSurat;

            $semesterVal = $validate['semester'] ?? null;
            if (empty($semesterVal)) {
                try {
                    $mhsApi = \App\Services\Mahasiswa::nim($validate['nim']);
                    if ($mhsApi && isset($mhsApi->semester)) {
                        $semesterVal = self::formatSemester($mhsApi->semester);
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to fetch student semester in update: " . $e->getMessage());
                }
            }
            $skt->semester = $semesterVal ?? $skt->semester ?? 'IX (Sembilan)';

            $skt->tahun_akademik = $validate['tahun_akademik'] ?? $skt->tahun_akademik;
            $skt->alamat = $validate['alamat'] ?? $skt->alamat;
            $skt->universitas_tujuan = $validate['universitas_tujuan'] ?? $skt->universitas_tujuan;
            $skt->tanggal = $validate['tanggal'];
            $skt->prodi_id = $validate['prodi_id'];
            $skt->jenis_kelamin = Auth::user()->jenis_kelamin;

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $skt->drive_file_id = null;
            $skt->drive_link = null;
            $skt->status = 'pending';
            $skt->save();

            // Re-fetch record with joins to build the new PDF
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
                    'fakultas.nidn_dekan as nidn_dekan',
                    'tanda_tangan.gambar as ttd',
                    'th_akademik.nama as th_akademik_nama',
                    'th_akademik.semester as th_akademik_semester'
                )
                ->where('surat_keterangan_transfer.id', $skt->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $pdf = Pdf::loadView('pdf.surat_keterangan_transfer', $pdfData)->setPaper('a4', 'portrait');

                $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganTransferController');
                $fileName = 'surat_keterangan_transfer_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Transfer';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->prodi_name, SuratKeteranganTransfer::class);
            }

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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

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
                    'fakultas.nidn_dekan as nidn_dekan',
                    'tanda_tangan.gambar as ttd',
                    'th_akademik.nama as th_akademik_nama',
                    'th_akademik.semester as th_akademik_semester'
                )
                ->where('surat_keterangan_transfer.id', $id)
                ->first();

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            $pdfData = $this->buildPdfData($data);

            $pdf = Pdf::loadView('pdf.surat_keterangan_transfer', $pdfData)->setPaper('a4', 'portrait');
            $prodiName = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->prodi_name ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiName . '/SuratKeteranganTransferController');
            $fileName = 'surat_keterangan_transfer_' . $data->nim . '_' . uniqid() . '.pdf';

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }
            $path = $directory . '/' . $fileName;

            $pdf->save($path);
            $data->update(['local_path' => $path]);

            $nameTable = 'Surat Keterangan Transfer';
            $googlePath = $data->prodi_name . '/' . $nameTable . '/' . $fileName;

            if (empty($data->drive_file_id)) {
                UploudSuratToDrive::dispatch($id, $nameTable, $data->prodi_name, SuratKeteranganTransfer::class);
            }

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error((string) $th);
            return response()->json(['status' => false, 'message' => 'Gagal generate PDF: Terjadi kesalahan pada server'], 500);
        }
    }

    private function resolveMahasiswaData($nim)
    {
        $mahasiswaData = [
            'tempat_lahir' => '',
            'alamat' => '',
            'semester' => ''
        ];

        try {
            $mhsApi = \App\Services\Mahasiswa::nim($nim);
            if ($mhsApi) {
                $mahasiswaData['tempat_lahir'] = $mhsApi->tempat_lahir ?? $mhsApi->tmp_lahir ?? '';
                $mahasiswaData['alamat'] = $mhsApi->alamat ?? $mhsApi->alamat_rumah ?? $mhsApi->jalan ?? '';
                if (isset($mhsApi->semester)) {
                    $mahasiswaData['semester'] = self::formatSemester($mhsApi->semester);
                }
            }
        } catch (\Throwable $e) {
            Log::error("Failed to fetch student from Simkeu API in SKT: " . $e->getMessage());
        }

        // Fallback local DB for tempat_lahir if still empty
        if (empty($mahasiswaData['tempat_lahir'])) {
            $mhsLocal = \App\Models\Mahasiswa::where('nim', $nim)->first();
            if ($mhsLocal) {
                $mahasiswaData['tempat_lahir'] = $mhsLocal->tempat_lahir ?? '';
            }
        }

        return $mahasiswaData;
    }

    private function buildPdfData($data)
    {
        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

        $stempelPath = base_path('../public_html/img/stempel.png');
        $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');

        $ttdPath = base_path('../public_html/' . $data->ttd);
        $ttdBase64 = \App\Services\SuratService::getBase64Image($ttdPath);

        $tahunAkademik = $data->th_akademik_nama ?? $data->tahun_akademik;
        $nama_fakultas = 'Fakultas ' . ucwords($data->fakultas_name);
        $universitasTujuan = $data->universitas_tujuan ?? 'perguruan tinggi lain';

        $tempatLahir = $data->tempat_lahir;
        $alamat = $data->alamat;
        $semester = $data->semester;

        // Resolve missing data from API if needed
        if (empty($tempatLahir) || empty($alamat) || empty($semester)) {
            $apiData = $this->resolveMahasiswaData($data->nim);
            $tempatLahir = $tempatLahir ?: $apiData['tempat_lahir'];
            $alamat = $alamat ?: $apiData['alamat'];
            $semester = $semester ?: $apiData['semester'];
        }

        $semester = $semester ?: 'IX (Sembilan)';

        return [
            'nomor' => $data->nomor,
            'nama' => $data->nama,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
            'tempat_lahir' => $tempatLahir,
            'nim' => $data->nim,
            'jurusan_prodi' => $data->jurusan_prodi,
            'semester' => $semester,
            'tahun_akademik' => $tahunAkademik,
            'nama_fakultas' => $nama_fakultas,
            'dekan' => $data->nama_ttd ?? $data->dekan,
            'nidn_dosen' => $data->nidn_dekan ?? $data->nidn ?? '',
            'tanggal' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'nama_kepala' => $data->nama_ttd ?? $data->nama_kepala,
            'nidn_kepala' => $data->nidn_kepala,
            'alamat' => $alamat,
            'universitas_tujuan' => $universitasTujuan,
            'kopBase64' => $kopBase64,
            'ttd' => $ttdBase64,
            'stempel' => $stempelBase64
        ];
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

    private static function formatSemester($semesterNum)
    {
        $map = [
            1 => 'I (Satu)',
            2 => 'II (Dua)',
            3 => 'III (Tiga)',
            4 => 'IV (Empat)',
            5 => 'V (Lima)',
            6 => 'VI (Enam)',
            7 => 'VII (Tujuh)',
            8 => 'VIII (Delapan)',
            9 => 'IX (Sembilan)',
            10 => 'X (Sepuluh)',
            11 => 'XI (Sebelas)',
            12 => 'XII (Dua Belas)',
            13 => 'XIII (Tiga Belas)',
            14 => 'XIV (Empat Belas)',
        ];

        $clean = filter_var($semesterNum, FILTER_SANITIZE_NUMBER_INT);
        if ($clean && isset($map[$clean])) {
            return $map[$clean];
        }

        return $semesterNum;
    }
}
