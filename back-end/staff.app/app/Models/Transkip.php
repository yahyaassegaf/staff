<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transkip extends Model
{
    protected $table = 'transkip';
    protected $guarded = [];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
