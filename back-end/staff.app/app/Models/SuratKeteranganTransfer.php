<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeteranganTransfer extends Model
{
    use HasFactory;

    protected $table = 'surat_keterangan_transfer';
    protected $guarded = [];

    public function tandaTangan(): BelongsTo
    {
        return $this->belongsTo(TandaTangan::class, 'tanda_tangan_id');
    }
}
