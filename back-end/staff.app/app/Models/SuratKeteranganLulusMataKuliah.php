<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeteranganLulusMataKuliah extends Model
{
    protected $table = 'surat_keterangan_lulus_mata_kuliah';

    protected $fillable = [
        'nomor_surat',
        'prodi_id',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'nim',
        'prodi_mahasiswa',
        'alamat_rumah',
        'kelas_pondok',
        'tanggal',
        'local_path',
        'drive_link',
        'drive_file_id',
        'status'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal' => 'date',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}
