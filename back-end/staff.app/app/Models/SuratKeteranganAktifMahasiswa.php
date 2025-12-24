<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeteranganAktifMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'surat_keterangan_aktif_mahasiswa';

    protected $guarded = [];
}
