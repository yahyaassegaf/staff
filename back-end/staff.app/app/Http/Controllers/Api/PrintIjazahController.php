<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Mahasiswa;
use App\Models\SettingJabatan;
use App\Models\TemplateIjazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintIjazahController extends Controller
{
    public function getBatchPrint($batch_id)
    {
        $batch = Batch::find($batch_id);
        if (!$batch) {
            return response()->json(['message' => 'Batch not found'], 404);
        }

        // Load mahasiswa beserta relasi prodi dan fakultas
        $mahasiswas = Mahasiswa::where('batch_id', $batch_id)
            ->with(['prodi', 'prodi.tandaTangan'])
            ->get();

        // Optimasi N+1 untuk Fakultas tanpa bergantung pada Eloquent Relation (karena struktur schema tabel pivot fakultas_prodi)
        $prodiIds = $mahasiswas->pluck('prodi_id')->unique()->filter()->toArray();
        $fakultasList = collect();
        if (!empty($prodiIds)) {
            $fakultasList = DB::table('fakultas_prodi')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->whereIn('fakultas_prodi.prodi_id', $prodiIds)
                ->select('fakultas_prodi.prodi_id', 'fakultas.nama', 'fakultas.alias', 'fakultas.nidn_dekan', 'fakultas.dekan')
                ->get()
                ->keyBy('prodi_id');
        }

        $templates = TemplateIjazah::with('posisiTemplate')
            ->where('is_active', 'aktif')
            ->get();

        // Ambil data tanda tangan/jabatan (rektor) dari setting_jabatan dengan relasi tanda_tangan
        $rektor = SettingJabatan::with('tandaTangan')
            ->where('kunci_jabatan', 'rektor')
            ->first();

        // Ambil data fakultas untuk relasi prodi
        $result = $mahasiswas->map(function ($mhs) use ($templates, $rektor, $fakultasList) {
            $prodiId = $mhs->prodi_id;
            $jenjang = $mhs->prodi ? $mhs->prodi->jenjang : null;

            // Logika resolusi dinamis (prioritas: prodi+jenjang > prodi only > jenjang only > global)
            $matchedTemplate = $templates->first(function ($t) use ($prodiId, $jenjang) {
                return $t->prodi_id == $prodiId && $t->jenjang == $jenjang;
            });

            if (!$matchedTemplate) {
                $matchedTemplate = $templates->first(function ($t) use ($prodiId) {
                    return $t->prodi_id == $prodiId && is_null($t->jenjang);
                });
            }

            if (!$matchedTemplate) {
                $matchedTemplate = $templates->first(function ($t) use ($jenjang) {
                    return is_null($t->prodi_id) && $t->jenjang == $jenjang;
                });
            }

            if (!$matchedTemplate) {
                $matchedTemplate = $templates->first(function ($t) {
                    return is_null($t->prodi_id) && is_null($t->jenjang);
                });
            }

            // Ambil data fakultas dari memori list yang dioptimasi
            $namaFakultas = null;
            if ($prodiId && $fakultasList->has($prodiId)) {
                $namaFakultas = $fakultasList->get($prodiId);
            }

            // Helper function untuk parsing tanggal
            $formatTanggal = function ($tgl) {
                if (empty($tgl)) return '';
                try {
                    // Coba parse dengan Carbon dan paksa ke bahasa Indonesia
                    return \Carbon\Carbon::parse($tgl)->locale('id')->isoFormat('D MMMM YYYY');
                } catch (\Exception $e) {
                    // Jika gagal parse (misal sudah berformat "02 Januari 2023"), kembalikan string aslinya
                    return $tgl;
                }
            };

            // Build mapping field mahasiswa ke field_name posisi_template
            $tglLahirFormatted = $formatTanggal($mhs->tgl_lahir);
            $tanggalKelulusanFormatted = $formatTanggal($mhs->tanggal_sk_yudisium);
            $tanggalIjazahFormatted = $formatTanggal($mhs->tanggal_penerbitan);

            // Peta field_name ke nilai aktual mahasiswa
            $fieldMap = [
                'nama_mahasiswa'           => $mhs->nama ?? '',
                'nim'                      => $mhs->nim ?? '',
                'nik'                      => $mhs->nik ?? '',
                'tgl_lahir'                => $tglLahirFormatted,
                'tempat_lahir'             => $mhs->tempat_lahir ?? '',
                'tempat_tanggal_lahir'     => trim(($mhs->tempat_lahir ?? '') . ', ' . $tglLahirFormatted, ', '),
                'nilai_akreditasi'         => $mhs->nilai_akreditasi ?? '',
                'nomor_sk_ban_pt'          => $mhs->nomor_sk_ban_pt ?? '',
                'nomor_ijazah_nasional'    => $mhs->nomor_ijazah_nasional ?? '',
                'tanggal_sk_yudisium'      => $tanggalKelulusanFormatted,
                'tanggal_ijazah'           => $tanggalIjazahFormatted,
                'tanggal_penerbitan'       => $tanggalIjazahFormatted,
                // Dari Prodi
                'program_studi'            => $mhs->prodi ? $mhs->prodi->nama : '',
                'jenjang'                  => $mhs->prodi ? $mhs->prodi->jenjang : '',
                'gelar'                    => $mhs->prodi ? ($mhs->prodi->gelar ?? '') : '',

                // Dari Fakultas
                'fakultas'                 => $namaFakultas ? $namaFakultas->nama : '',
                'nama_fakultas'            => $namaFakultas ? $namaFakultas->nama : '',
                'alias_fakultas'           => $namaFakultas ? ($namaFakultas->alias ?? '') : '',

                // Program Studi dan Fakultas (Gabungan 1 Baris)
                'prodi_fakultas_tgl_lulus'   => '<strong>Program Studi ' . ($mhs->prodi ? $mhs->prodi->nama : '') . ' Fakultas ' . ($namaFakultas ? $namaFakultas->nama : '') . '</strong><span style="font-weight: normal !important;"> pada tanggal ' . $tanggalKelulusanFormatted . '</span>',

                // Dekan (dari fakultas)
                'nama_dekan'               => $namaFakultas ? ($namaFakultas->dekan ?: 'DEBUG: DEKAN KOSONG DI DB') : 'DEBUG: RELASI FAKULTAS-PRODI TIDAK ADA',
                'nidn_dekan'               => $namaFakultas && !empty($namaFakultas->nidn_dekan) ? 'NIDN. ' . $namaFakultas->nidn_dekan : '',

                // Rektor (dari setting_jabatan)
                'nama_rektor'              => $rektor && $rektor->tandaTangan ? ($rektor->tandaTangan->nama ?? '') : '',
                'nidn_rektor'              => $rektor && !empty($rektor->nidn) ? 'NIDN. ' . $rektor->nidn : '',

                // Kota (bisa dikonfigurasi atau dari prodi)
                'kota_tempat'              => 'Bangil',
                'kota_tanggal_ijazah'      => 'Bangil, ' . $tanggalIjazahFormatted,
            ];

            \Illuminate\Support\Facades\Log::info('Debug PrintIjazahController kota_tanggal_ijazah:', ['value' => $fieldMap['kota_tanggal_ijazah'] ?? 'NULL']);

            return [
                'mahasiswa'  => $fieldMap,
                'raw_mahasiswa' => $mhs,
                'template'   => $matchedTemplate ? [
                    'id'              => $matchedTemplate->id,
                    'nama_template'   => $matchedTemplate->nama_template,
                    'file_background' => $matchedTemplate->file_background,
                    'ukuran_kertas'   => $matchedTemplate->ukuran_kertas,
                    'orientasi'       => $matchedTemplate->orientasi,
                    'teks_statis'     => $matchedTemplate->teks_statis,
                    'posisi'          => $matchedTemplate->posisiTemplate,
                ] : null,
            ];
        });

        return response()->json([
            'batch' => $batch,
            'data'  => $result
        ]);
    }
}
