<?php

namespace App\Services;

class SuratService
{
    protected static string $kodeSurat = 'PP.00';
    protected static string $institusi = 'UII.085';

    static function NoSuratKeteranganLulusMataKuliah(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeteranganTasmaKknPpl($nomor)
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

    static function NoSuratKeteranganAktifMahasiswa($nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }
    // SU- 5192 /UII.085/FT/TL.00/11/2025
    public function NoSuratFakultas(int $nomor, string $unit)
    {
        $kodeSurat = 'TL.00';
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/{$this->institusi}/{$unit}/{$kodeSurat}/{$bulan}/{$tahun}";
    }



    static function NoSuratPernyataanMelakukanVerifikasiNilaiMahasiswa(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeterangan(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');

        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratTugas(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratKeteranganTransfer(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    static function NoSuratIzinPenelitian(int $nomor, string $unit)
    {
        $bulan = date('m');
        $tahun = date('Y');
        return "SU-{$nomor}/" . self::$institusi . "/{$unit}/" . self::$kodeSurat . "/{$bulan}/{$tahun}";
    }

    // public function () : Returntype {

    // }

}
