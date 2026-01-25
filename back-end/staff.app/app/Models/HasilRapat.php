<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilRapat extends Model
{
    use HasFactory;

    protected $table = 'hasil_rapat';

    protected $fillable = [
        'nomor_surat',
        'prodi_id',
        'agenda',
        'tanggal',
        'waktu',
        'tempat',
        'pembahasan',
        'local_path',
        'drive_link',
        'drive_file_id',
        'status'
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(AnggotaRapat::class, 'hasil_rapat_id');
    }
}
