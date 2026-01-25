<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPernyataanVerifikasiNilai extends Model
{
    use HasFactory;

    protected $table = 'surat_pernyataan_verifikasi_nilai';

    protected $guarded = [];

    public function tandaTangan()
    {
        return $this->belongsTo(TandaTangan::class, 'tanda_tangan_id');
    }
}
