<?php

namespace App\Services;

class SuratService
{
    public static function formatNomorSurat(string $aliasJenisSurat, string $nomor, $tanggal, $prodiId): string
    {
        $jenisSurat = \App\Models\JenisSurat::where('alias', $aliasJenisSurat)->first();
        if (!$jenisSurat) {
            return $nomor;
        }

        $formatStr = $jenisSurat->format_surat;

        // Cari semua tag yang diapit kurung kurawal, termasuk yang mengandung tanda hubung (misal ALIAS-FAKULTAS)
        preg_match_all('/\{([A-Z_-]+)\}/', $formatStr, $matches);
        $tags = $matches[1];

        $tgl = \Carbon\Carbon::parse($tanggal);

        // Ambil data prodi beserta fakultasnya (hanya jika diperlukan oleh tag)
        $prodi = \App\Models\Prodi::find($prodiId);
        $prodiAlias = $prodi ? ($prodi->alias ?? '') : '';

        $aliasFakultas = '';
        if (in_array('ALIAS-FAKULTAS', $tags)) {
            $fak = \Illuminate\Support\Facades\DB::table('fakultas_prodi')
                ->join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
                ->where('fakultas_prodi.prodi_id', $prodiId)
                ->select('fakultas.alias', 'fakultas.nama')
                ->first();
            $aliasFakultas = $fak ? $fak->alias : '';
        }

        $replacements = [];
        foreach ($tags as $tag) {
            $replacements["{{$tag}}"] = match ($tag) {
                'NO'              => $nomor,
                'TGL'             => $tgl->format('d'),
                'BULAN'           => $tgl->format('m'),
                'TAHUN'           => $tgl->format('Y'),
                'PRODI'           => $prodiAlias,
                'ALIAS-FAKULTAS'  => $aliasFakultas,
                default           => "{{$tag}}", // biarkan tag asli jika tidak dikenali
            };
        }

        return str_replace(array_keys($replacements), array_values($replacements), $formatStr);
    }
    protected static string $kodeSurat = 'PP.00';
    protected static string $institusi = 'UII.085';

    static function NoSuratKeteranganLulusMataKuliah(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeteranganTasmaKknPpl(string $nomor)
    {
        $unit = 'LPKM';
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeteranganQismulAman($nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeteranganAktifMahasiswa(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }
    // SU- 5192 /UII.085/FT/TL.00/11/2025
    public function NoSuratFakultas(string $nomor, string $unit)
    {
        $kodeSurat = 'TL.00';
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/{$this->institusi}/{$unit}/{$kodeSurat}/{$bulan}/{$tahun}";
    }



    static function NoSuratPernyataanMelakukanVerifikasiNilaiMahasiswa(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeterangan(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratTugas(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeteranganTransfer(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratIzinPenelitian(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        $kodeSurat = 'TL.00';
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoHasilRapat(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }
    static function NoSuratKeteranganAdministrasiKeuangan(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        // Nomor: SU- 1479/UII.085/BAK/KU.01.2/05/2025
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/KU.01.2/{$bulan}/{$tahun}";
    }
    static function NoSuratKomprehensif(string $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    /**
     * Mengonversi file gambar menjadi format Base64 secara aman.
     * Mengembalikan null jika file tidak ditemukan, kosong, atau berupa direktori (mencegah crash).
     */
    public static function getBase64Image(?string $path, string $defaultMime = 'image/jpeg'): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Normalisasi path
        $path = realpath($path) ?: $path;

        if (file_exists($path) && is_file($path) && is_readable($path)) {
            try {
                $content = file_get_contents($path);
                if ($content !== false) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    $mime = $defaultMime;
                    if ($ext === 'png') {
                        $mime = 'image/png';
                    } elseif ($ext === 'gif') {
                        $mime = 'image/gif';
                    } elseif ($ext === 'svg') {
                        $mime = 'image/svg+xml';
                    }
                    return 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal encode gambar di path {$path}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Mengonversi format tanggal menjadi bahasa Indonesia (d F Y)
     * secara aman dan melakukan koreksi otomatis untuk penulisan bulan.
     */
    public static function formatTanggalIndonesian($tanggal): string
    {
        if (empty($tanggal)) {
            return '';
        }

        try {
            $formatted = \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
            
            // Koreksi teks bulan bahasa Indonesia yang sering keliru/tercampur bahasa Inggris
            $replacements = [
                'Mey' => 'Mei',
                'mey' => 'mei',
                'May' => 'Mei',
                'may' => 'mei',
                'August' => 'Agustus',
                'august' => 'agustus',
                'October' => 'Oktober',
                'october' => 'oktober',
                'December' => 'Desember',
                'december' => 'desember',
            ];
            
            return str_replace(array_keys($replacements), array_values($replacements), $formatted);
        } catch (\Throwable $th) {
            return (string) $tanggal;
        }
    }
}
