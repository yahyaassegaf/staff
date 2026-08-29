<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TanggalNilai extends Model
{
    protected $table = 'tanggal_nilai';
    protected $guarded = [];

    public function nilaiMahasiswa()
    {
        return $this->hasMany(NilaiMahasiswa::class, 'tanggal_nilai_id');
    }
}
