<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeterangan extends Model
{
    use HasFactory;

    protected $table = 'surat_keterangan';
    protected $guarded = [];

    public function tandaTangan()
    {
        return $this->belongsTo(TandaTangan::class, 'tanda_tangan_id');
    }
}
