<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\UploudSuratToDrive;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use App\Models\SuratKeteranganAktifMahasiswa;
use App\Models\ThAkademik;
use App\Services\SuratService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeteranganAktifMahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $data = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id');

        $data->select(
            'surat_keterangan_aktif_mahasiswa.*',
            'prodi.nama as nama_prodi',
            'prodi.alias as alias_prodi',
            'prodi.nama_kepala as nama_kepala_prodi'
        );

        if ($request->filled("prodi_id")) {
            $data->where('surat_keterangan_aktif_mahasiswa.prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('surat_keterangan_aktif_mahasiswa.prodi_id', $login->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('tahun_akademik', 'like', "%{$search}%");
            });
        }

        $auth = Auth::user()->jenis_kelamin;
        if ($auth == 'L') {
            $data->where('surat_keterangan_aktif_mahasiswa.jenis_kelamin', 'L');
        } else {
            $data->where('surat_keterangan_aktif_mahasiswa.jenis_kelamin', 'P');
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
                'no_surat' => 'required|string|max:255|unique:nomor,nomor',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',

                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'prodi_mhs' => 'required|string|max:255',
                'semester' => 'required|string|max:50',
                'tahun_akademik' => 'required|string|max:100',
                'nama_ortu' => 'required|string|max:255',

                'alamat_ortu' => 'required|string',
                'hp_ortu' => 'nullable|string|max:50',
                'tanggal' => 'required|date',
                'petanda_tangan' => 'nullable|in:ya,tidak',
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

            $noSurat = SuratService::formatNomorSurat('SKAM', $no_surat, $validate['tanggal'], $validate['prodi_id']);

            $skam = new SuratKeteranganAktifMahasiswa();
            $skam->nomor_surat = $noSurat;
            $skam->prodi_id = $validate['prodi_id'];
            $skam->th_akademik_id = null;
            $skam->nama_lengkap = $validate['nama_mhs'];
            $skam->nim = $validate['nim'];
            $skam->nik = null;
            $skam->tempat_lahir = $validate['tempat_lahir'];
            $skam->tanggal_lahir = $validate['tanggal_lahir'];
            $skam->prodi_mhs = $validate['prodi_mhs'];
            $skam->semester = $validate['semester'];
            $skam->tahun_akademik = $validate['tahun_akademik'];
            $skam->nama_ortu = $validate['nama_ortu'];
            $skam->nik_ortu = null;
            $skam->nip_ortu = null;
            $skam->alamat_ortu = $validate['alamat_ortu'];
            $skam->hp_ortu = $validate['hp_ortu'] ?? null;
            $skam->tanggal = $validate['tanggal'];
            $skam->user_id = Auth::user()->id;
            $skam->jenis_kelamin = Auth::user()->jenis_kelamin;
            $skam->status = 'pending';
            $skam->petanda_tangan = $validate['petanda_tangan'] ?? 'tidak';
            $skam->save();

            $Nomor              = new NoSurat();
            $Nomor->nomor = $no_surat;
            $Nomor->user_id     = Auth::user()->id;
            $Nomor->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_keterangan_aktif_mahasiswa.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'fakultas.nama as fakultas',
                    'fakultas.dekan as dekan',
                    'fakultas.nidn_dekan as nidn_dekan',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd')
                )
                ->where('surat_keterangan_aktif_mahasiswa.id', $skam->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $prodiFolder = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganAktifMahasiswaController');
                $pdf = Pdf::loadView('pdf.surat_aktif', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_aktif_mahasiswa_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Aktif Mahasiswa';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganAktifMahasiswa::class);
            }

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
        $skam = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id')
            ->select(
                'surat_keterangan_aktif_mahasiswa.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi'
            )
            ->where('surat_keterangan_aktif_mahasiswa.id', $id)
            ->first();

        if (!$skam) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }


        $nomorStr = $skam->nomor_surat ?? $skam->nomor ?? null;
        if ($nomorStr) {
            $parts = explode('/', $nomorStr);
            $firstPart = $parts[0];
            if (strpos($firstPart, '-') !== false) {
                $firstPart = substr($firstPart, strpos($firstPart, '-') + 1);
            }
            $skam->no_surat = trim($firstPart);
        }

        return response()->json([
            'status' => true,
            'data' => $skam,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'no_surat' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($id) {
                        $skam = \App\Models\SuratKeteranganAktifMahasiswa::find($id);
                        if ($skam) {
                            $originalNoSurat = '';
                            $nomorStr = $skam->nomor_surat ?? $skam->nomor ?? null;
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
                'prodi_id' => 'required|exists:prodi,id',
                'nama_mhs' => 'required|string|max:255',
                'nim' => 'required|string|max:255',

                'tempat_lahir' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'prodi_mhs' => 'required|string|max:255',
                'semester' => 'required|string|max:50',
                'tahun_akademik' => 'required|string|max:100',
                'nama_ortu' => 'required|string|max:255',

                'alamat_ortu' => 'required|string',
                'hp_ortu' => 'nullable|string|max:50',
                'tanggal' => 'required|date',
                'petanda_tangan' => 'nullable|in:ya,tidak',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $skam = SuratKeteranganAktifMahasiswa::find($id);
            if (!$skam) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $oldDriveFileId = $skam->drive_file_id;
            $oldLocalPath = $skam->local_path;

            // Map frontend fields (if different) to database fields
            $dataToUpdate = [
                'prodi_id'       => $validate['prodi_id'],
                'th_akademik_id' => null,
                'nama_lengkap'   => $validate['nama_mhs'],
                'nim'            => $validate['nim'],
                'nik'            => null,
                'tempat_lahir'   => $validate['tempat_lahir'],
                'tanggal_lahir'  => $validate['tanggal_lahir'],
                'prodi_mhs'      => $validate['prodi_mhs'],
                'semester'       => $validate['semester'],
                'tahun_akademik' => $validate['tahun_akademik'],
                'nama_ortu'      => $validate['nama_ortu'],
                'nik_ortu'       => null,
                'nip_ortu'       => null,
                'alamat_ortu'    => $validate['alamat_ortu'],
                'hp_ortu'        => $validate['hp_ortu'] ?? null,
                'tanggal'        => $validate['tanggal'],
                'jenis_kelamin'  => Auth::user()->jenis_kelamin,
                'user_id'        => Auth::user()->id,
                'petanda_tangan' => $validate['petanda_tangan'] ?? 'tidak',
            ];

            $skam->fill($dataToUpdate);

            $noSurat = \App\Services\SuratService::formatNomorSurat('SKAM', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id']);
            $skam->nomor_surat = $noSurat;

            // Delete old file from Google Drive if exists
            if (!empty($oldDriveFileId)) {
                \App\Services\GoogleDrive::deleteFile($oldDriveFileId);
            }
            if (!empty($oldLocalPath) && file_exists($oldLocalPath)) {
                @unlink($oldLocalPath);
            }

            $skam->drive_file_id = null;
            $skam->drive_link = null;
            $skam->status = 'pending';
            $skam->save();

            // Re-fetch record with joins to build the new PDF
            $data = SuratKeteranganAktifMahasiswa::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_aktif_mahasiswa.prodi_id')
                ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
                ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
                ->select(
                    'surat_keterangan_aktif_mahasiswa.*',
                    'prodi.nama as nama_prodi',
                    'prodi.alias as alias_prodi',
                    'fakultas.nama as fakultas',
                    'fakultas.dekan as dekan',
                    'fakultas.nidn_dekan as nidn_dekan',
                    \DB::raw('COALESCE(tanda_tangan.tdd, tanda_tangan.gambar) as ttd')
                )
                ->where('surat_keterangan_aktif_mahasiswa.id', $skam->id)
                ->first();

            if ($data) {
                $pdfData = $this->buildPdfData($data);
                $prodiFolder = Auth::user()?->prodi ? Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganAktifMahasiswaController');
                $pdf = Pdf::loadView('pdf.surat_aktif', $pdfData)->setPaper('a4', 'portrait');
                $fileName = 'surat_keterangan_aktif_mahasiswa_' . $data->nim . '_' . uniqid() . '.pdf';

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $data->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan Aktif Mahasiswa';
                UploudSuratToDrive::dispatch($data->id, $nameTable, $data->nama_prodi, SuratKeteranganAktifMahasiswa::class);
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
            $skam = SuratKeteranganAktifMahasiswa::find($id);
            if (!$skam) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            // $noSurat = \App\Services\SuratService::formatNomorSurat('SKAM', $validate['no_surat'], $validate['tanggal'], $validate['prodi_id'] ?? null);
            // $data->nomor_surat = $noSurat;



            $skam->delete();

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
            $data = SuratKeteranganAktifMahasiswa::find($id);

            if (!$data) {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            if (empty($data->local_path) || !file_exists($data->local_path)) {
                return response()->json(['status' => false, 'message' => 'File PDF tidak ditemukan di server'], 404);
            }

            $fileName = basename($data->local_path);

            return response()->file($data->local_path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }

    private function buildPdfData($data)
    {
        $stempelBase64 = '';
        $tddBase64 = '';

        if (isset($data->petanda_tangan) && $data->petanda_tangan === 'ya') {
            $stempelPath = base_path('../public_html/img/stempel.png');
            if (file_exists($stempelPath)) {
                $stempelBase64 = SuratService::getBase64Image($stempelPath);
            }

            if (!empty($data->ttd)) {
                if (str_starts_with($data->ttd, 'data:image')) {
                    $tddBase64 = $data->ttd;
                } else {
                    $tddPath = base_path('../public_html/' . $data->ttd);
                    if (file_exists($tddPath)) {
                        $tddBase64 = SuratService::getBase64Image($tddPath);
                    }
                }
            }
        }

        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = SuratService::getBase64Image($kopPath);

        return [
            'nomor_surat' => $data->nomor_surat,
            'nama' => $data->nama_lengkap,
            'nim' => $data->nim,
            'nik' => $data->nik,
            'tempat_lahir' => $data->tempat_lahir,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
            'prodi_mhs' => $data->prodi_mhs,
            'semester' => $data->semester,
            'tahun_akademik' => $data->tahun_akademik,
            'nama_ortu' => $data->nama_ortu,
            'nik_ortu' => $data->nik_ortu,
            'alamat_ortu' => $data->alamat_ortu,
            'hp_ortu' => $data->hp_ortu,
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
            'nama_prodi' => $data->nama_prodi,
            'nama_kepala_prodi' => $data->nama_kepala_prodi,
            'nidn_kepala_prodi' => $data->nidn_kepala_prodi,
            'dekan' => $data->nama_kepala_prodi ? $data->nama_kepala_prodi : $data->dekan,
            'fakultas' => $data->fakultas,
            'nidn_dekan' => $data->nidn_kepala_prodi ? $data->nidn_kepala_prodi : $data->nidn_dekan,
            'kopBase64' => $kopBase64,
            'stempel' => $stempelBase64,
            'ttd' => $tddBase64,
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
