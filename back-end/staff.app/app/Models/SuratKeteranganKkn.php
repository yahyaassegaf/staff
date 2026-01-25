<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeteranganKkn extends Model
{
    use HasFactory;

    protected $table = 'surat_keterangan_kkn';

    protected $fillable = [
        'nomor_surat',
        'ketua',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'nim',
        'prodi_id',
        'user_id',
        'prodi_mhs',
        'alamat_rumah',
        'kelas_pondok',
        'tanggal',
        'jenis_kelamin',
        'drive_file_id',
        'local_path',
        'drive_link',
        'status',
    ];
}
