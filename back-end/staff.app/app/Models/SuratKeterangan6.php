<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeterangan6 extends Model
{
    use HasFactory;

    protected $table = 'surat_keterangan_6';

    protected $guarded = ['id'];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function skLulusMk()
    {
        return $this->belongsTo(SuratKeteranganLulusMataKuliah::class, 'surat_keterangan_lulus_mata_kuliah_id');
    }

    public function skAdminKeuangan()
    {
        return $this->belongsTo(SuratKeteranganAdministrasiKeuangan::class, 'surat_keterangan_administrasi_keuangan_id');
    }

    public function skTasmaKknPpl()
    {
        return $this->belongsTo(SuratKeteranganTasmaKknPpl::class, 'surat_keterangan_tasma_kkn_ppl_id');
    }

    public function skUjianKomprehensifDiniyah()
    {
        return $this->belongsTo(SuratKeteranganUjianKomprehensifDiniyah::class, 'surat_keterangan_ujian_komprehensif_diniyah_id');
    }

    public function skQismulAman()
    {
        return $this->belongsTo(SuratKeteranganQismulAman::class, 'surat_keterangan_qismul_aman_id');
    }

    // Dummy mutator untuk status karena surat_keterangan_6 tidak memiliki kolom status di database.
    // Menghindari QueryException saat job upload mengupdate status.
    public function setStatusAttribute($value)
    {
        // Diabaikan dengan aman
    }
}
