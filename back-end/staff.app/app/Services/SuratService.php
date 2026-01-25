<?php

namespace App\Services;

class SuratService
{
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
}
