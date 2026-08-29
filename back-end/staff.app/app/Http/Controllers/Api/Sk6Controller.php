<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SuratKeterangan6;
use App\Models\SuratKeteranganLulusMataKuliah;
use App\Models\SuratKeteranganAdministrasiKeuangan;
use App\Models\SuratKeteranganTasmaKknPpl;
use App\Models\SuratKeteranganQismulAman;
use App\Models\SuratKeteranganUjianKomprehensifDiniyah;
use App\Models\JenisSurat;
use App\Models\LogSurat;
use App\Models\NoSurat;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class Sk6Controller extends Controller
{


    public function index(Request $request)
    {
        $data = SuratKeterangan6::with([
            'prodi',
            'skLulusMk'
        ]);

        if ($request->filled("prodi_id")) {
            $data->where('prodi_id', $request->prodi_id);
        }

        $login = Auth::user()->prodi;
        if ($login) {
            $data->where('prodi_id', $login->id);
        }

        // Filter berdasarkan jenis kelamin mungkin memerlukan join ke tabel detail jika tidak disimpan di tabel master
        $auth = Auth::user()->jenis_kelamin;
        $data->whereHas('skLulusMk', function ($query) use ($auth) {
            $query->where('jenis_kelamin', $auth);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('nim', 'like', "%{$search}%")
                    ->orWhere('nama_mhs', 'like', "%{$search}%")
                    ->orWhereHas('prodi', function ($qProdi) use ($search) {
                        $qProdi->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'desc')
        );

        $data = $data->paginate($request->input('limit', 10));

        $formattedData = $data->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_lengkap' => $item->nama_mhs,
                'nim' => $item->nim,
                'drive_link' => $item->drive_link,
                'created_at' => $item->created_at,
            ];
        });

        // HACK: because `tap($data->clone())->getCollection()->transform` doesn't always apply back nicely on pagination
        // Let's manually rebuild the paginator array response
        $responsePagination = [
            'current_page' => $data->currentPage(),
            'data' => $formattedData,
            'first_page_url' => $data->url(1),
            'from' => $data->firstItem(),
            'last_page' => $data->lastPage(),
            'last_page_url' => $data->url($data->lastPage()),
            'links' => $data->linkCollection()->toArray(),
            'next_page_url' => $data->nextPageUrl(),
            'path' => $data->path(),
            'per_page' => $data->perPage(),
            'prev_page_url' => $data->previousPageUrl(),
            'to' => $data->lastItem(),
            'total' => $data->total(),
        ];

        return response()->json([
            'status' => true,
            'data' => $responsePagination,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function store(Request $request)
    {
        Log::info($request->all());

        $validator = Validator::make($request->all(), [
            'prodi_id' => 'required',
            'nama_mhs' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'nim' => 'required|string|max:255',
            'prodi_mhs' => 'required|string|max:255',
            'alamat_rumah' => 'required|string',
            'kelas_pondok' => 'required|string|max:255',
            'tanggal' => 'nullable|date',
            'no_sklmk' => 'required|string|unique:nomor,nomor',
            'no_skak' => 'required|string|unique:nomor,nomor',
            'no_sktkp' => 'required|string|unique:nomor,nomor',
            'no_skqa' => 'required|string|unique:nomor,nomor',
            'no_skukd' => 'required|string|unique:nomor,nomor',
            'tanggal_berlaku_dari' => 'nullable|date',
            'tanggal_berlaku_sampai' => 'nullable|date',
        ], [
            'no_sklmk.unique' => 'Nomor surat sudah terpakai',
            'no_skak.unique' => 'Nomor surat sudah terpakai',
            'no_sktkp.unique' => 'Nomor surat sudah terpakai',
            'no_skqa.unique' => 'Nomor surat sudah terpakai',
            'no_skukd.unique' => 'Nomor surat sudah terpakai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validate = $validator->validated();
        $tanggal = $validate['tanggal'] ?? date('Y-m-d');
        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Generate nomor surat lengkap dari format jenis_surat
            $nomorSklmk = \App\Services\SuratService::formatNomorSurat('SKLM', $validate['no_sklmk'], $tanggal, $validate['prodi_id']);
            $nomorSkak  = \App\Services\SuratService::formatNomorSurat('SKAK', $validate['no_skak'], $tanggal, $validate['prodi_id']);
            $nomorSktkp = \App\Services\SuratService::formatNomorSurat('STTKP', $validate['no_sktkp'], $tanggal, $validate['prodi_id']);
            $nomorSkqa  = \App\Services\SuratService::formatNomorSurat('SKQA', $validate['no_skqa'], $tanggal, $validate['prodi_id']);
            $nomorSkukd = \App\Services\SuratService::formatNomorSurat('SKUKD', $validate['no_skukd'], $tanggal, $validate['prodi_id']);

            // 1. SK Lulus Mata Kuliah
            $sklmk = new SuratKeteranganLulusMataKuliah();
            $sklmk->nomor_surat = $nomorSklmk;
            $sklmk->prodi_id = $validate['prodi_id'];
            $sklmk->nama_lengkap = $validate['nama_mhs'];
            $sklmk->tempat_lahir = $validate['tempat_lahir'];
            $sklmk->tanggal_lahir = $validate['tanggal_lahir'];
            $sklmk->nim = $validate['nim'];
            $sklmk->prodi_mahasiswa = $validate['prodi_mhs'];
            $sklmk->alamat_rumah = $validate['alamat_rumah'];
            $sklmk->kelas_pondok = $validate['kelas_pondok'];
            $sklmk->tanggal = $tanggal;
            $sklmk->user_id = $user->id;
            $sklmk->jenis_kelamin = $user->jenis_kelamin;
            $sklmk->petanda_tangan = 'tidak';
            $sklmk->status = 'pending';
            $sklmk->save();

            $nomorSklmkObj = new NoSurat();
            $nomorSklmkObj->nomor = $validate['no_sklmk'];
            $nomorSklmkObj->user_id = $user->id;
            $nomorSklmkObj->save();

            $logSklmk = new LogSurat();
            $logSklmk->nomor = $validate['no_sklmk'];
            $logSklmk->nomor_surat = $nomorSklmk;
            $logSklmk->nama_surat = 'Surat Keterangan Lulus Mata Kuliah';
            $logSklmk->user_id = $user->id;
            $logSklmk->save();

            // 2. SK Administrasi Keuangan
            $skak = new SuratKeteranganAdministrasiKeuangan();
            $skak->nomor_surat = $nomorSkak;
            $skak->prodi_id = $validate['prodi_id'];
            $skak->nama_lengkap = $validate['nama_mhs'];
            $skak->tempat_lahir = $validate['tempat_lahir'];
            $skak->tanggal_lahir = $validate['tanggal_lahir'];
            $skak->nim = $validate['nim'];
            $skak->prodi_mhs = $validate['prodi_mhs'];
            $skak->alamat_rumah = $validate['alamat_rumah'];
            $skak->kelas_pondok = $validate['kelas_pondok'];
            $skak->tanggal = $tanggal;
            $skak->user_id = $user->id;
            $skak->jenis_kelamin = $user->jenis_kelamin;
            $skak->petanda_tangan = 'tidak';
            $skak->status = 'pending';
            $skak->save();

            $nomorSkakObj = new NoSurat();
            $nomorSkakObj->nomor = $validate['no_skak'];
            $nomorSkakObj->user_id = $user->id;
            $nomorSkakObj->save();

            $logSkak = new LogSurat();
            $logSkak->nomor = $validate['no_skak'];
            $logSkak->nomor_surat = $nomorSkak;
            $logSkak->nama_surat = 'Surat Keterangan Administrasi Keuangan';
            $logSkak->user_id = $user->id;
            $logSkak->save();

            // 3. SK Tasma, KKN, PPL
            $sktkp = new SuratKeteranganTasmaKknPpl();
            $sktkp->nomor_surat = $nomorSktkp;
            $sktkp->prodi_id = $validate['prodi_id'];

            $settingTasma = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_tasma')->first();
            if ($settingTasma && $settingTasma->tandaTangan) {
                $sktkp->ketua = $settingTasma->tandaTangan->nama;
            }

            $sktkp->nama_lengkap = $validate['nama_mhs'];
            $sktkp->tempat_lahir = $validate['tempat_lahir'];
            $sktkp->tanggal_lahir = $validate['tanggal_lahir'];
            $sktkp->nim = $validate['nim'];
            $sktkp->prodi_mhs = $validate['prodi_mhs'];
            $sktkp->alamat_rumah = $validate['alamat_rumah'];
            $sktkp->kelas_pondok = $validate['kelas_pondok'];
            $sktkp->tanggal = $tanggal;
            $sktkp->user_id = $user->id;
            $sktkp->jenis_kelamin = $user->jenis_kelamin;
            $sktkp->petanda_tangan = 'tidak';
            $sktkp->status = 'pending';
            $sktkp->save();

            $nomorSktkpObj = new NoSurat();
            $nomorSktkpObj->nomor = $validate['no_sktkp'];
            $nomorSktkpObj->user_id = $user->id;
            $nomorSktkpObj->save();

            $logSktkp = new LogSurat();
            $logSktkp->nomor = $validate['no_sktkp'];
            $logSktkp->nomor_surat = $nomorSktkp;
            $logSktkp->nama_surat = 'Surat Keterangan TASMA, KKN, PPL';
            $logSktkp->user_id = $user->id;
            $logSktkp->save();

            // 4. SK Qismul Aman
            $skqa = new SuratKeteranganQismulAman();
            $skqa->nomor_surat = $nomorSkqa;
            $skqa->prodi_id = $validate['prodi_id'];

            $settingQa = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_qismul_aman')->first();
            if ($settingQa && $settingQa->tandaTangan) {
                $skqa->ketua = $settingQa->tandaTangan->nama;
            }
            $skqa->nama_lengkap = $validate['nama_mhs'];
            $skqa->tempat_lahir = $validate['tempat_lahir'];
            $skqa->tanggal_lahir = $validate['tanggal_lahir'];
            $skqa->nim = $validate['nim'];
            $skqa->prodi_mhs = $validate['prodi_mhs'];
            $skqa->alamat_rumah = $validate['alamat_rumah'];
            $skqa->kelas_pondok = $validate['kelas_pondok'];
            $skqa->tanggal = $tanggal;
            $skqa->tanggal_berlaku_dari = $validate['tanggal_berlaku_dari'] ?? null;
            $skqa->tanggal_berlaku_sampai = $validate['tanggal_berlaku_sampai'] ?? null;
            $skqa->user_id = $user->id;
            $skqa->jenis_kelamin = $user->jenis_kelamin;
            $skqa->petanda_tangan = 'tidak';
            $skqa->status = 'pending';
            $skqa->save();

            $nomorSkqaObj = new NoSurat();
            $nomorSkqaObj->nomor = $validate['no_skqa'];
            $nomorSkqaObj->user_id = $user->id;
            $nomorSkqaObj->save();

            $logSkqa = new LogSurat();
            $logSkqa->nomor = $validate['no_skqa'];
            $logSkqa->nomor_surat = $nomorSkqa;
            $logSkqa->nama_surat = 'Surat Keterangan Qismul Aman';
            $logSkqa->user_id = $user->id;
            $logSkqa->save();

            // 5. SK Ujian Komprehensif Diniyah
            $skukd = new SuratKeteranganUjianKomprehensifDiniyah();
            $skukd->nomor_surat = $nomorSkukd;
            $skukd->prodi_id = $validate['prodi_id'];

            $settingKomprehensif = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
            if ($settingKomprehensif && $settingKomprehensif->tandaTangan) {
                $skukd->tanda_tangan_id = $settingKomprehensif->tanda_tangan_id;
                $skukd->koor_komprehensif = $settingKomprehensif->tandaTangan->nama;
            }

            $skukd->nama_lengkap = $validate['nama_mhs'];
            $skukd->tempat_lahir = $validate['tempat_lahir'];
            $skukd->tanggal_lahir = $validate['tanggal_lahir'];
            $skukd->nim = $validate['nim'];
            $skukd->prodi_mhs = $validate['prodi_mhs'];
            $skukd->alamat_rumah = $validate['alamat_rumah'];
            $skukd->kelas_pondok = $validate['kelas_pondok'];
            $skukd->tanggal = $tanggal;
            $skukd->user_id = $user->id;
            $skukd->jenis_kelamin = $user->jenis_kelamin;
            $skukd->petanda_tangan = 'tidak';
            $skukd->status = 'pending';
            $skukd->save();

            $nomorSkukdObj = new NoSurat();
            $nomorSkukdObj->nomor = $validate['no_skukd'];
            $nomorSkukdObj->user_id = $user->id;
            $nomorSkukdObj->save();

            $logSkukd = new LogSurat();
            $logSkukd->nomor = $validate['no_skukd'];
            $logSkukd->nomor_surat = $nomorSkukd;
            $logSkukd->nama_surat = 'Surat Keterangan Ujian Komprehensif Diniyah';
            $logSkukd->user_id = $user->id;
            $logSkukd->save();

            // 6. Header SK 6
            $sk6 = new SuratKeterangan6();
            $sk6->nama_mhs = $validate['nama_mhs'];
            $sk6->nim = $validate['nim'];
            $sk6->tanggal = $tanggal;
            $sk6->prodi_id = $validate['prodi_id'];
            $sk6->surat_keterangan_lulus_mata_kuliah_id = $sklmk->id;
            $sk6->surat_keterangan_administrasi_keuangan_id = $skak->id;
            $sk6->surat_keterangan_tasma_kkn_ppl_id = $sktkp->id;
            $sk6->surat_keterangan_ujian_komprehensif_diniyah_id = $skukd->id;
            $sk6->surat_keterangan_qismul_aman_id = $skqa->id;
            $sk6->save();

            DB::commit();
            // Re-fetch record and details to build the new PDF
            $sklmkRefetched = $this->getSklmkDetail($sk6->surat_keterangan_lulus_mata_kuliah_id);
            if ($sklmkRefetched) {
                $pdfData = $this->buildSk6PdfData($sk6, $sklmkRefetched);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sk_6', $pdfData)->setPaper('a4', 'portrait');

                $fileName = 'SK6_' . $sklmkRefetched->nim . '_' . uniqid() . '.pdf';
                $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($sklmkRefetched->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/Sk6Controller');

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $sk6->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan 6';
                \App\Jobs\UploudSuratToDrive::dispatch($sk6->id, $nameTable, $sklmkRefetched->nama_prodi, SuratKeterangan6::class);

                $this->dispatchGoogleDriveJobs($sk6, $sklmkRefetched->nama_prodi, $path);
            }

            return response()->json([
                'status' => true,
                'message' => '5 Surat Keterangan berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $sk6 = SuratKeterangan6::with([
            'skLulusMk',
            'skAdminKeuangan',
            'skTasmaKknPpl',
            'skUjianKomprehensifDiniyah',
            'skQismulAman'
        ])->find($id);

        if (!$sk6) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $sklmk = $sk6->skLulusMk;
        $skak = $sk6->skAdminKeuangan;
        $sktkp = $sk6->skTasmaKknPpl;
        $skukd = $sk6->skUjianKomprehensifDiniyah;
        $skqa = $sk6->skQismulAman;

        $data = [
            'id' => $sk6->id,
            'prodi_id' => $sk6->prodi_id,
            'nama_mhs' => $sk6->nama_mhs,
            'tempat_lahir' => $sklmk ? $sklmk->tempat_lahir : '',
            'tanggal_lahir' => $sklmk ? $sklmk->tanggal_lahir : '',
            'nim' => $sk6->nim,
            'prodi_mhs' => $sklmk ? $sklmk->prodi_mahasiswa : '',
            'alamat_rumah' => $sklmk ? $sklmk->alamat_rumah : '',
            'kelas_pondok' => $sklmk ? $sklmk->kelas_pondok : '',
            'tanggal' => $sk6->tanggal,
            'nomor_surat_sklmk' => $sklmk ? $sklmk->nomor_surat : '',
            'nomor_surat_skak' => $skak ? $skak->nomor_surat : '',
            'nomor_surat_sktkp' => $sktkp ? $sktkp->nomor_surat : '',
            'nomor_surat_skqa' => $skqa ? $skqa->nomor_surat : '',
            'nomor_surat_skukd' => $skukd ? $skukd->nomor_surat : '',
            'id_skak' => $skak ? $skak->id : null,
            'id_sktkp' => $sktkp ? $sktkp->id : null,
            'id_skqa' => $skqa ? $skqa->id : null,
            'id_skukd' => $skukd ? $skukd->id : null,
            'tanggal_berlaku_dari' => $skqa ? $skqa->tanggal_berlaku_dari : null,
            'tanggal_berlaku_sampai' => $skqa ? $skqa->tanggal_berlaku_sampai : null,
        ];

        return response()->json([
            'status' => true,
            'data' => (object)$data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function update(Request $request, $id)
    {
        // Log::info($request->all());

        $validator = Validator::make($request->all(), [
            'prodi_id' => 'required',
            'nama_mhs' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'nim' => 'required|string|max:255',
            'prodi_mhs' => 'required|string|max:255',
            'alamat_rumah' => 'required|string',
            'kelas_pondok' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'no_sklmk' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $sk6 = \App\Models\SuratKeterangan6::find($id);
                    if ($sk6 && $sk6->surat_keterangan_lulus_mata_kuliah_id) {
                        $model = \App\Models\SuratKeteranganLulusMataKuliah::find($sk6->surat_keterangan_lulus_mata_kuliah_id);
                        if ($model) {
                            $originalNoSurat = '';
                            $nomorStr = $model->nomor_surat ?? $model->nomor ?? null;
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
                }
            ],
            'no_skak' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $sk6 = \App\Models\SuratKeterangan6::find($id);
                    if ($sk6 && $sk6->surat_keterangan_administrasi_keuangan_id) {
                        $model = \App\Models\SuratKeteranganAdministrasiKeuangan::find($sk6->surat_keterangan_administrasi_keuangan_id);
                        if ($model) {
                            $originalNoSurat = '';
                            $nomorStr = $model->nomor_surat ?? $model->nomor ?? null;
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
                }
            ],
            'no_sktkp' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $sk6 = \App\Models\SuratKeterangan6::find($id);
                    if ($sk6 && $sk6->surat_keterangan_tasma_kkn_ppl_id) {
                        $model = \App\Models\SuratKeteranganTasmaKknPpl::find($sk6->surat_keterangan_tasma_kkn_ppl_id);
                        if ($model) {
                            $originalNoSurat = '';
                            $nomorStr = $model->nomor_surat ?? $model->nomor ?? null;
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
                }
            ],
            'no_skqa' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $sk6 = \App\Models\SuratKeterangan6::find($id);
                    if ($sk6 && $sk6->surat_keterangan_qismul_aman_id) {
                        $model = \App\Models\SuratKeteranganQismulAman::find($sk6->surat_keterangan_qismul_aman_id);
                        if ($model) {
                            $originalNoSurat = '';
                            $nomorStr = $model->nomor_surat ?? $model->nomor ?? null;
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
                }
            ],
            'no_skukd' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $sk6 = \App\Models\SuratKeterangan6::find($id);
                    if ($sk6 && $sk6->surat_keterangan_ujian_komprehensif_diniyah_id) {
                        $model = \App\Models\SuratKeteranganUjianKomprehensifDiniyah::find($sk6->surat_keterangan_ujian_komprehensif_diniyah_id);
                        if ($model) {
                            $originalNoSurat = '';
                            $nomorStr = $model->nomor_surat ?? $model->nomor ?? null;
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
                }
            ],
            'tanggal_berlaku_dari' => 'nullable|date',
            'tanggal_berlaku_sampai' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validate = $validator->validated();

        $sk6 = SuratKeterangan6::find($id);
        if (!$sk6) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $tanggal = $validate['tanggal'];

            // Update Header SK 6
            $sk6->nama_mhs = $validate['nama_mhs'];
            $sk6->nim = $validate['nim'];
            $sk6->tanggal = $tanggal;
            $sk6->prodi_id = $validate['prodi_id'];
            $sk6->save();

            // Generate nomor surat lengkap dari format jenis_surat
            $nomorSklmk = \App\Services\SuratService::formatNomorSurat('SKLM', $validate['no_sklmk'], $tanggal, $validate['prodi_id']);
            $nomorSkak  = \App\Services\SuratService::formatNomorSurat('SKAK', $validate['no_skak'], $tanggal, $validate['prodi_id']);
            $nomorSktkp = \App\Services\SuratService::formatNomorSurat('STTKP', $validate['no_sktkp'], $tanggal, $validate['prodi_id']);
            $nomorSkqa  = \App\Services\SuratService::formatNomorSurat('SKQA', $validate['no_skqa'], $tanggal, $validate['prodi_id']);
            $nomorSkukd = \App\Services\SuratService::formatNomorSurat('SKUKD', $validate['no_skukd'], $tanggal, $validate['prodi_id']);

            // Ambil data detail
            $sklmk = SuratKeteranganLulusMataKuliah::find($sk6->surat_keterangan_lulus_mata_kuliah_id);
            $skak = SuratKeteranganAdministrasiKeuangan::find($sk6->surat_keterangan_administrasi_keuangan_id);
            $sktkp = SuratKeteranganTasmaKknPpl::find($sk6->surat_keterangan_tasma_kkn_ppl_id);
            $skukd = SuratKeteranganUjianKomprehensifDiniyah::find($sk6->surat_keterangan_ujian_komprehensif_diniyah_id);
            $skqa = SuratKeteranganQismulAman::find($sk6->surat_keterangan_qismul_aman_id);

            $oldDriveFileIdSk6 = $sk6->drive_file_id;
            $oldDriveFileIdSklmk = $sklmk ? $sklmk->drive_file_id : null;
            $oldDriveFileIdSkak = $skak ? $skak->drive_file_id : null;
            $oldDriveFileIdSktkp = $sktkp ? $sktkp->drive_file_id : null;
            $oldDriveFileIdSkqa = $skqa ? $skqa->drive_file_id : null;
            $oldDriveFileIdSkukd = $skukd ? $skukd->drive_file_id : null;

            if ($sklmk) {

                $sklmk->nomor_surat = $nomorSklmk;
                $sklmk->prodi_id = $validate['prodi_id'];
                $sklmk->nama_lengkap = $validate['nama_mhs'];
                $sklmk->tempat_lahir = $validate['tempat_lahir'];
                $sklmk->tanggal_lahir = $validate['tanggal_lahir'];
                $sklmk->nim = $validate['nim'];
                $sklmk->prodi_mahasiswa = $validate['prodi_mhs'];
                $sklmk->alamat_rumah = $validate['alamat_rumah'];
                $sklmk->kelas_pondok = $validate['kelas_pondok'];
                $sklmk->tanggal = $tanggal;
                $sklmk->jenis_kelamin = $user->jenis_kelamin;
                $sklmk->petanda_tangan = 'tidak';
                $sklmk->status = 'pending';
                $sklmk->save();
            }

            if ($skak) {

                $skak->nomor_surat = $nomorSkak;
                $skak->prodi_id = $validate['prodi_id'];
                $skak->nama_lengkap = $validate['nama_mhs'];
                $skak->tempat_lahir = $validate['tempat_lahir'];
                $skak->tanggal_lahir = $validate['tanggal_lahir'];
                $skak->nim = $validate['nim'];
                $skak->prodi_mhs = $validate['prodi_mhs'];
                $skak->alamat_rumah = $validate['alamat_rumah'];
                $skak->kelas_pondok = $validate['kelas_pondok'];
                $skak->tanggal = $tanggal;
                $skak->jenis_kelamin = $user->jenis_kelamin;
                $skak->petanda_tangan = 'tidak';
                $skak->status = 'pending';
                $skak->save();
            }

            if ($sktkp) {

                $sktkp->nomor_surat = $nomorSktkp;
                $sktkp->prodi_id = $validate['prodi_id'];
                $sktkp->nama_lengkap = $validate['nama_mhs'];
                $sktkp->tempat_lahir = $validate['tempat_lahir'];
                $sktkp->tanggal_lahir = $validate['tanggal_lahir'];
                $sktkp->nim = $validate['nim'];
                $sktkp->prodi_mhs = $validate['prodi_mhs'];
                $sktkp->alamat_rumah = $validate['alamat_rumah'];
                $sktkp->kelas_pondok = $validate['kelas_pondok'];
                $sktkp->tanggal = $tanggal;
                $sktkp->jenis_kelamin = $user->jenis_kelamin;
                $sktkp->petanda_tangan = 'tidak';
                $sktkp->status = 'pending';
                $sktkp->save();
            }

            if ($skqa) {
                $skqa->nomor_surat = $nomorSkqa;
                $skqa->prodi_id = $validate['prodi_id'];

                $settingQa = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_qismul_aman')->first();
                if ($settingQa && $settingQa->tandaTangan) {
                    $skqa->ketua = $settingQa->tandaTangan->nama;
                }

                $skqa->nama_lengkap = $validate['nama_mhs'];
                $skqa->tempat_lahir = $validate['tempat_lahir'];
                $skqa->tanggal_lahir = $validate['tanggal_lahir'];
                $skqa->nim = $validate['nim'];
                $skqa->prodi_mhs = $validate['prodi_mhs'];
                $skqa->alamat_rumah = $validate['alamat_rumah'];
                $skqa->kelas_pondok = $validate['kelas_pondok'];
                $skqa->tanggal = $tanggal;
                $skqa->tanggal_berlaku_dari = $validate['tanggal_berlaku_dari'] ?? null;
                $skqa->tanggal_berlaku_sampai = $validate['tanggal_berlaku_sampai'] ?? null;
                $skqa->jenis_kelamin = $user->jenis_kelamin;
                $skqa->petanda_tangan = 'tidak';
                $skqa->status = 'pending';
                $skqa->save();
            }

            if ($skukd) {
                $skukd->nomor_surat = $nomorSkukd;
                $skukd->prodi_id = $validate['prodi_id'];

                $settingKomprehensif = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
                if ($settingKomprehensif && $settingKomprehensif->tandaTangan) {
                    $skukd->tanda_tangan_id = $settingKomprehensif->tanda_tangan_id;
                    $skukd->koor_komprehensif = $settingKomprehensif->tandaTangan->nama;
                }

                $skukd->nama_lengkap = $validate['nama_mhs'];
                $skukd->tempat_lahir = $validate['tempat_lahir'];
                $skukd->tanggal_lahir = $validate['tanggal_lahir'];
                $skukd->nim = $validate['nim'];
                $skukd->prodi_mhs = $validate['prodi_mhs'];
                $skukd->alamat_rumah = $validate['alamat_rumah'];
                $skukd->kelas_pondok = $validate['kelas_pondok'];
                $skukd->tanggal = $tanggal;
                $skukd->jenis_kelamin = $user->jenis_kelamin;
                $skukd->petanda_tangan = 'tidak';
                $skukd->status = 'pending';
                $skukd->save();
            }

            // Delete old files from Google Drive if exists
            if (!empty($oldDriveFileIdSk6)) \App\Services\GoogleDrive::deleteFile($oldDriveFileIdSk6);
            if (!empty($oldDriveFileIdSklmk)) \App\Services\GoogleDrive::deleteFile($oldDriveFileIdSklmk);
            if (!empty($oldDriveFileIdSkak)) \App\Services\GoogleDrive::deleteFile($oldDriveFileIdSkak);
            if (!empty($oldDriveFileIdSktkp)) \App\Services\GoogleDrive::deleteFile($oldDriveFileIdSktkp);
            if (!empty($oldDriveFileIdSkqa)) \App\Services\GoogleDrive::deleteFile($oldDriveFileIdSkqa);
            if (!empty($oldDriveFileIdSkukd)) \App\Services\GoogleDrive::deleteFile($oldDriveFileIdSkukd);

            $sk6->drive_file_id = null;
            $sk6->drive_link = null;
            $sk6->status = 'pending';
            $sk6->save();

            if ($sklmk) $sklmk->update(['drive_file_id' => null, 'drive_link' => null, 'status' => 'pending']);
            if ($skak) $skak->update(['drive_file_id' => null, 'drive_link' => null, 'status' => 'pending']);
            if ($sktkp) $sktkp->update(['drive_file_id' => null, 'drive_link' => null, 'status' => 'pending']);
            if ($skqa) $skqa->update(['drive_file_id' => null, 'drive_link' => null, 'status' => 'pending']);
            if ($skukd) $skukd->update(['drive_file_id' => null, 'drive_link' => null, 'status' => 'pending']);

            DB::commit();

            // Re-fetch record and details to build the new PDF
            $sklmkRefetched = $this->getSklmkDetail($sk6->surat_keterangan_lulus_mata_kuliah_id);
            if ($sklmkRefetched) {
                $pdfData = $this->buildSk6PdfData($sk6, $sklmkRefetched);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sk_6', $pdfData)->setPaper('a4', 'portrait');

                $fileName = 'SK6_' . $sklmkRefetched->nim . '_' . uniqid() . '.pdf';
                $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($sklmkRefetched->nama_prodi ?? 'UMUM');
                $directory = base_path('../public_html/pdf/' . $prodiFolder . '/Sk6Controller');

                if (!\Illuminate\Support\Facades\File::exists($directory)) {
                    \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
                }

                $path = $directory . '/' . $fileName;
                $pdf->save($path);

                $sk6->update(['local_path' => $path]);

                $nameTable = 'Surat Keterangan 6';
                \App\Jobs\UploudSuratToDrive::dispatch($sk6->id, $nameTable, $sklmkRefetched->nama_prodi, SuratKeterangan6::class);

                $this->dispatchGoogleDriveJobs($sk6, $sklmkRefetched->nama_prodi, $path);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
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
            $sk6 = SuratKeterangan6::find($id);
            if (!$sk6) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            DB::beginTransaction();

            // Simpan ID detail sebelum header dihapus
            $id_sklmk = $sk6->surat_keterangan_lulus_mata_kuliah_id;
            $id_skak = $sk6->surat_keterangan_administrasi_keuangan_id;
            $id_sktkp = $sk6->surat_keterangan_tasma_kkn_ppl_id;
            $id_skukd = $sk6->surat_keterangan_ujian_komprehensif_diniyah_id;
            $id_skqa = $sk6->surat_keterangan_qismul_aman_id;

            // Hapus headernya terlebih dahulu karena memegang Foreign Key
            $sk6->delete();

            // Hapus detailnya
            if ($id_sklmk) SuratKeteranganLulusMataKuliah::where('id', $id_sklmk)->delete();
            if ($id_skak) SuratKeteranganAdministrasiKeuangan::where('id', $id_skak)->delete();
            if ($id_sktkp) SuratKeteranganTasmaKknPpl::where('id', $id_sktkp)->delete();
            if ($id_skukd) SuratKeteranganUjianKomprehensifDiniyah::where('id', $id_skukd)->delete();
            if ($id_skqa) SuratKeteranganQismulAman::where('id', $id_skqa)->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => '5 Surat Keterangan berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }

    public function downloadPdf($id)
    {
        try {
            $data = SuratKeterangan6::find($id);

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
            \Illuminate\Support\Facades\Log::error($th->getMessage());
            return response()->json(['status' => false, 'message' => 'Gagal download PDF']);
        }
    }

    private function getSklmkDetail($id)
    {
        return SuratKeteranganLulusMataKuliah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
            ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
            ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->leftJoin('tanda_tangan as tt_prodi', 'tt_prodi.id', '=', 'prodi.tanda_tangan_id')
            ->select(
                'surat_keterangan_lulus_mata_kuliah.*',
                'prodi.nama as nama_prodi',
                'fakultas.nama as fakultas',
                'prodi.alias as alias_prodi',
                'prodi.nama_kepala as nama_kepala_prodi',
                'tt_prodi.nama as kaprodi_nama',
                'tt_prodi.gambar as kaprodi_gambar',
                'tt_prodi.tdd as kaprodi_tdd'
            )
            ->where('surat_keterangan_lulus_mata_kuliah.id', $id)
            ->first();
    }

    private function getTtdJabatan($key)
    {
        $setting = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', $key)->first();
        if ($setting && $setting->tandaTangan) {
            $ttdBase64 = '';
            if ($setting->tandaTangan->gambar) {
                $ttdBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $setting->tandaTangan->gambar));
            } elseif ($setting->tandaTangan->tdd) {
                $ttdBase64 = $setting->tandaTangan->tdd;
            }
            return [
                'nama' => $setting->tandaTangan->nama,
                'ttd' => $ttdBase64,
                'nama_jabatan' => $setting->nama_jabatan,
            ];
        }
        return ['nama' => '', 'ttd' => '', 'nama_jabatan' => ''];
    }

    private function buildSk6PdfData($sk6, $sklmk)
    {
        $skak = $sk6->skAdminKeuangan;
        $sktkp = $sk6->skTasmaKknPpl;
        $skukd = $sk6->skUjianKomprehensifDiniyah;
        $skqa = $sk6->skQismulAman;

        $kopPath = base_path('../public_html/img/kop.jpg');
        $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

        $kaprodiTtdBase64 = '';
        if ($sklmk->kaprodi_gambar) {
            $kaprodiTtdBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $sklmk->kaprodi_gambar));
        } elseif ($sklmk->kaprodi_tdd) {
            $kaprodiTtdBase64 = $sklmk->kaprodi_tdd;
        }

        $ttdSkak = $this->getTtdJabatan('kepala_biro_keuangan');
        $ttdSktkp = $this->getTtdJabatan('ketua_tasma');

        if ($skukd && $skukd->tanda_tangan_id) {
            $tandaTangan = \App\Models\TandaTangan::find($skukd->tanda_tangan_id);
            $ttdBase64 = '';
            if ($tandaTangan) {
                if ($tandaTangan->gambar) {
                    $ttdBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $tandaTangan->gambar));
                } elseif ($tandaTangan->tdd) {
                    $ttdBase64 = $tandaTangan->tdd;
                }
                $ttdSkukd = [
                    'nama' => $tandaTangan->nama,
                    'ttd' => $ttdBase64,
                ];
            } else {
                $ttdSkukd = $this->getTtdJabatan('ketua_komprehensif');
            }
        } else {
            $ttdSkukd = $this->getTtdJabatan('ketua_komprehensif');
        }

        $ttdSkqa = $this->getTtdJabatan('ketua_qismul_aman');

        $stempelPath = base_path('../public_html/img/stempel.png');
        $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath);

        return [
            'nomor_surat_sklmk' => $sklmk->nomor_surat,
            'nomor_surat_skak' => $skak ? $skak->nomor_surat : '-',
            'nomor_surat_sktkp' => $sktkp ? $sktkp->nomor_surat : '-',
            'nomor_surat_skukd' => $skukd ? $skukd->nomor_surat : '-',
            'nomor_surat_skqa' => $skqa ? $skqa->nomor_surat : '-',

            'nama' => $sklmk->nama_lengkap,
            'tempat_lahir' => $sklmk->tempat_lahir,
            'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($sklmk->tanggal_lahir),
            'nim' => $sklmk->nim,
            'fakultas' => $sklmk->fakultas,
            'prodi' => $sklmk->nama_prodi,
            'alamat' => $sklmk->alamat_rumah,
            'kelas' => $sklmk->kelas_pondok,
            'alias_prodi' => $sklmk->alias_prodi,
            'nama_kepala_prodi' => $sklmk->nama_kepala_prodi,
            'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($sklmk->tanggal),
            'kopBase64' => $kopBase64,
            'tanggal_awal' => $skqa && $skqa->tanggal_berlaku_dari ? \App\Services\SuratService::formatTanggalIndonesian($skqa->tanggal_berlaku_dari) : '-',
            'tanggal_akhir' => $skqa && $skqa->tanggal_berlaku_sampai ? \App\Services\SuratService::formatTanggalIndonesian($skqa->tanggal_berlaku_sampai) : '-',

            // Signatures
            'kaprodi_nama' => $sklmk->kaprodi_nama ?: $sklmk->nama_kepala_prodi,
            'kaprodi_ttd' => $kaprodiTtdBase64,

            'skak_nama' => $ttdSkak['nama'] ?: 'Dr. Musleh Harry, SH, M.Hum',
            'skak_ttd' => $ttdSkak['ttd'],

            'sktkp_nama' => $ttdSktkp['nama'] ?: 'Achmad Djuaini, M,Pd',
            'sktkp_ttd' => $ttdSktkp['ttd'],

            'skukd_nama' => $ttdSkukd['nama'] ?: 'Dr. Habib Zainal Abidin Bilfaqih, M.Pd.',
            'skukd_ttd' => $ttdSkukd['ttd'],

            'skqa_nama' => $ttdSkqa['nama'] ?: 'Ust. Fathul Munif',
            'skqa_ttd' => $ttdSkqa['ttd'],
            'stempel' => $stempelBase64,
            'petanda_tangan' => 'tidak',
        ];
    }

    private function dispatchGoogleDriveJobs($sk6, $namaProdi, $localPath)
    {
        $models = [
            [
                'id' => $sk6->surat_keterangan_lulus_mata_kuliah_id,
                'class' => \App\Models\SuratKeteranganLulusMataKuliah::class,
                'name' => 'Surat Keterangan Lulus Mata Kuliah',
                'generator' => 'generateSklmkPdf'
            ],
            [
                'id' => $sk6->surat_keterangan_administrasi_keuangan_id,
                'class' => \App\Models\SuratKeteranganAdministrasiKeuangan::class,
                'name' => 'Surat Keterangan Administrasi Keuangan',
                'generator' => 'generateSkakPdf'
            ],
            [
                'id' => $sk6->surat_keterangan_tasma_kkn_ppl_id,
                'class' => \App\Models\SuratKeteranganTasmaKknPpl::class,
                'name' => 'Surat Keterangan TASMA, KKN, PPL',
                'generator' => 'generateSktkpPdf'
            ],
            [
                'id' => $sk6->surat_keterangan_ujian_komprehensif_diniyah_id,
                'class' => \App\Models\SuratKeteranganUjianKomprehensifDiniyah::class,
                'name' => 'Surat Keterangan Ujian Komprehensif Diniyah',
                'generator' => 'generateSkukdPdf'
            ],
            [
                'id' => $sk6->surat_keterangan_qismul_aman_id,
                'class' => \App\Models\SuratKeteranganQismulAman::class,
                'name' => 'Surat Keterangan Qismul Aman',
                'generator' => 'generateSkqaPdf'
            ]
        ];

        foreach ($models as $item) {
            if (!$item['id']) continue;

            $generator = $item['generator'];
            $individualPath = $this->$generator($item['id']);

            if ($individualPath) {
                $googlePath = $namaProdi . '/' . $item['name'] . '/' . basename($individualPath);
                $subModel = $item['class']::find($item['id']);
                if ($subModel && empty($subModel->drive_file_id)) {
                    \App\Jobs\UploudSuratToDrive::dispatch($item['id'], $item['name'], $namaProdi, $item['class']);
                }
            }
        }
    }

    private function generateSklmkPdf($id)
    {
        $data = SuratKeteranganLulusMataKuliah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_lulus_mata_kuliah.prodi_id')
            ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
            ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'fakultas.tanda_tangan_id')
            ->select(
                'surat_keterangan_lulus_mata_kuliah.*',
                'prodi.nama as nama_prodi',
                'fakultas.nama as fakultas',
                'fakultas.dekan as dekan',
                'fakultas.nidn_dekan as nidn_dekan',
                'prodi.alias as alias_prodi',
                'prodi.nidn_kepala as nidn_kepala_prodi',
                'prodi.nama_kepala as nama_kepala_prodi',
                'tanda_tangan.gambar as ttd'
            )
            ->where('surat_keterangan_lulus_mata_kuliah.id', $id)
            ->first();

        if ($data) {
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath);

            $tddBase64 = '';
            if ($data->ttd) {
                $tddBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $data->ttd));
            }

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                'nim' => $data->nim,
                'fakultas' => $data->fakultas,
                'prodi' => $data->nama_prodi,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'alias_prodi' => $data->alias_prodi,
                'dekan' => $data->dekan,
                'nidn_dekan' => $data->nidn_dekan,
                'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'stempel' => $stempelBase64,
                'petanda_tangan' => 'tidak',

                'ttd' => $tddBase64,
                'kopBase64' => $kopBase64,
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.v_surat_keterangan_lulus_mata_kuliah', $pdfData)->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_lulus_mata_kuliah_' . $data->nim . '_' . $data->prodi_mahasiswa . '_' . uniqid() . '.pdf';
            $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganLulusMataKuliahController');

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            SuratKeteranganLulusMataKuliah::where('id', $id)->update(['local_path' => $path]);
            return $path;
        }
        return null;
    }

    private function generateSkakPdf($id)
    {
        $data = SuratKeteranganAdministrasiKeuangan::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_administrasi_keuangan.prodi_id')
            ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
            ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_administrasi_keuangan.tanda_tangan_id')
            ->select(
                'surat_keterangan_administrasi_keuangan.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'fakultas.nama as fakultas',
                'tanda_tangan.nama as nama_ttd',
                'tanda_tangan.gambar as ttd'
            )
            ->where('surat_keterangan_administrasi_keuangan.id', $id)
            ->first();

        if ($data) {
            $settingKeuangan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'kepala_biro_keuangan')->first();
            $namaKetua = $settingKeuangan && $settingKeuangan->tandaTangan ? $settingKeuangan->tandaTangan->nama : ($data->nama_ttd ?? $data->kepala_biro);
            $ttdKetua = $settingKeuangan && $settingKeuangan->tandaTangan ? $settingKeuangan->tandaTangan->gambar : $data->ttd;
            $namaJabatan = $settingKeuangan ? $settingKeuangan->nama_jabatan : 'Kepala Biro Administrasi Keuangan';

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

            $tddPath = base_path('../public_html/' . $ttdKetua);
            $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath);

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                'nim' => $data->nim,
                'fakultas' => $data->fakultas ?? '-',
                'prodi' => $data->prodi_mhs ?? '-',
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'nama_penandatangan' => $namaKetua,
                'jabatan_penandatangan' => $namaJabatan,
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
                'petanda_tangan' => 'tidak',

            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.administrasi_keuangan', $pdfData)->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_administrasi_keuangan_' . $data->nim . '_' . uniqid() . '.pdf';
            $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganAdministrasiKeuanganController/');

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            SuratKeteranganAdministrasiKeuangan::where('id', $id)->update(['local_path' => $path]);
            return $path;
        }
        return null;
    }

    private function generateSktkpPdf($id)
    {
        $data = SuratKeteranganTasmaKknPpl::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_tasma_kkn_ppl.prodi_id')
            ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
            ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_tasma_kkn_ppl.tanda_tangan_id')
            ->select(
                'fakultas.nama as fakultas',
                'surat_keterangan_tasma_kkn_ppl.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'tanda_tangan.nama as nama_ttd',
                'tanda_tangan.gambar as ttd'
            )
            ->where('surat_keterangan_tasma_kkn_ppl.id', $id)
            ->first();

        if ($data) {
            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

            $settingTasma = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_tasma')->first();
            $namaKetua = 'Ketua TASMA';
            $tddBase64 = '';

            if ($settingTasma && $settingTasma->tandaTangan) {
                $namaKetua = $settingTasma->tandaTangan->nama;
                if ($settingTasma->tandaTangan->gambar) {
                    $tddBase64 = \App\Services\SuratService::getBase64Image(base_path('../public_html/' . $settingTasma->tandaTangan->gambar));
                } elseif ($settingTasma->tandaTangan->tdd) {
                    $tddBase64 = $settingTasma->tandaTangan->tdd;
                }
            }

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'ketua' => $namaKetua,
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
                'petanda_tangan' => 'tidak',

            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat_tasma_kkn_ppl', $pdfData)->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_tasma_kkn_ppl_' . $data->nim . '_' . uniqid() . '.pdf';
            $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganTasmaKknPplController');

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            SuratKeteranganTasmaKknPpl::where('id', $id)->update(['local_path' => $path]);
            return $path;
        }
        return null;
    }

    private function generateSkukdPdf($id)
    {
        $data = SuratKeteranganUjianKomprehensifDiniyah::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.prodi_id')
            ->leftJoin('fakultas_prodi', 'fakultas_prodi.prodi_id', '=', 'prodi.id')
            ->leftJoin('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'surat_keterangan_ujian_komprehensif_diniyah.tanda_tangan_id')
            ->select(
                'surat_keterangan_ujian_komprehensif_diniyah.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'fakultas.nama as fakultas',
                'tanda_tangan.nama as nama_ttd',
                'tanda_tangan.gambar as ttd'
            )
            ->where('surat_keterangan_ujian_komprehensif_diniyah.id', $id)
            ->first();

        if ($data) {
            $namaKetua = $data->nama_ttd ?? $data->koor_komprehensif;
            $ttdKetua = $data->ttd;

            if (!$namaKetua) {
                $settingKompre = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_komprehensif')->first();
                $namaKetua = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->nama : null;
                $ttdKetua = $settingKompre && $settingKompre->tandaTangan ? $settingKompre->tandaTangan->gambar : null;
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

            $tddPath = base_path('../public_html/' . $ttdKetua);
            $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);

            $stempelPath = base_path('../public_html/img/stempel.png');
            $stempelBase64 = \App\Services\SuratService::getBase64Image($stempelPath, 'image/png');

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                'nim' => $data->nim,
                'fakultas' => $data->fakultas,
                'prodi' => $data->nama_prodi,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'nama_penandatangan' => $namaKetua,
                'jabatan_penandatangan' => 'Ketua / Koordinator Komprehensip',
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
                'stempel' => $stempelBase64,
                'petanda_tangan' => 'tidak',
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.komprehensif', $pdfData)->setPaper('a4', 'portrait');

            $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganUjianKomprehensifDiniyahController/');
            $fileName = 'surat_keterangan_ujian_komprehensif_diniyah_' . $data->nim . '_' . $data->alias_prodi . '_' . uniqid() . '.pdf';

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . $fileName;
            $pdf->save($path);

            SuratKeteranganUjianKomprehensifDiniyah::where('id', $id)->update(['local_path' => $path]);
            return $path;
        }
        return null;
    }

    private function generateSkqaPdf($id)
    {
        $data = SuratKeteranganQismulAman::leftJoin('prodi', 'prodi.id', '=', 'surat_keterangan_qismul_aman.prodi_id')
            ->leftJoin('tanda_tangan', 'tanda_tangan.id', '=', 'prodi.tanda_tangan_id')
            ->select(
                'surat_keterangan_qismul_aman.*',
                'prodi.nama as nama_prodi',
                'prodi.alias as alias_prodi',
                'tanda_tangan.gambar as ttd'
            )
            ->where('surat_keterangan_qismul_aman.id', $id)
            ->first();

        if ($data) {
            $settingJabatan = \App\Models\SettingJabatan::with('tandaTangan')->where('kunci_jabatan', 'ketua_qismul_aman')->first();
            $tddBase64 = '';
            if ($settingJabatan && $settingJabatan->tandaTangan && $settingJabatan->tandaTangan->gambar) {
                $tddPath = base_path('../public_html/' . $settingJabatan->tandaTangan->gambar);
                if (file_exists($tddPath)) {
                    $tddBase64 = \App\Services\SuratService::getBase64Image($tddPath);
                }
            }

            $kopPath = base_path('../public_html/img/kop.jpg');
            $kopBase64 = \App\Services\SuratService::getBase64Image($kopPath);

            $pdfData = [
                'nomor_surat' => $data->nomor_surat,
                'ketua' => $data->ketua ?? ($settingJabatan && $settingJabatan->tandaTangan ? $settingJabatan->tandaTangan->nama : null),
                'nama' => $data->nama_lengkap,
                'tempat_lahir' => $data->tempat_lahir,
                'tanggal_lahir' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_lahir),
                'nim' => $data->nim,
                'prodi' => $data->prodi_mhs,
                'alamat' => $data->alamat_rumah,
                'kelas' => $data->kelas_pondok,
                'jenis_kelamin' => $data->jenis_kelamin,
                'tanggal_berlaku_dari' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_berlaku_dari),
                'tanggal_berlaku_sampai' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal_berlaku_sampai),
                'tanggal_surat' => \App\Services\SuratService::formatTanggalIndonesian($data->tanggal),
                'kopBase64' => $kopBase64,
                'ttd' => $tddBase64,
            ];

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.surat_qismul_aman', $pdfData)->setPaper('a4', 'portrait');

            $fileName = 'surat_keterangan_qismul_aman_' . $data->nim . '_' . uniqid() . '.pdf';
            $prodiFolder = \Illuminate\Support\Facades\Auth::user()?->prodi ? \Illuminate\Support\Facades\Auth::user()->prodi->nama : ($data->nama_prodi ?? 'UMUM');
            $directory = base_path('../public_html/pdf/' . $prodiFolder . '/SuratKeteranganQismulAmanController');

            if (!\Illuminate\Support\Facades\File::exists($directory)) {
                \Illuminate\Support\Facades\File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $fileName;
            $pdf->save($path);

            SuratKeteranganQismulAman::where('id', $id)->update(['local_path' => $path]);
            return $path;
        }
        return null;
    }
}
