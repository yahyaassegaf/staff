<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThAkademik extends Model
{
    use HasFactory;

    protected $table = 'th_akademik';

    protected $fillable = [
        'kode',
        'nama',
        'semester',
        'aktif',
        'user_id',
        'id_awal',
        'token',
    ];
}
