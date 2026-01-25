<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeteranganAdministrasiKeuangan extends Model
{
    use HasFactory;

    protected $table = 'surat_keterangan_administrasi_keuangan';
    protected $guarded = [];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function tandaTangan(): BelongsTo
    {
        return $this->belongsTo(TandaTangan::class, 'tanda_tangan_id');
    }
}
