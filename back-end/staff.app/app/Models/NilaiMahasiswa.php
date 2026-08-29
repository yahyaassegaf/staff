<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiMahasiswa extends Model
{
    protected $table = 'nilai_mahasiswa';
    protected $guarded = [];

    public function tanggalNilai()
    {
        return $this->belongsTo(TanggalNilai::class, 'tanggal_nilai_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
