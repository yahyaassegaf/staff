<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    protected $table = 'fakultas';
    protected $guarded = [];

    protected $casts = [
        'tanda_tangan_id' => 'array',
    ];

    public function FakultasProdi()
    {
        return $this->hasMany(Prodi::class);
    }
}
