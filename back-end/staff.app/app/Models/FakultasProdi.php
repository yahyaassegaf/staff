<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakultasProdi extends Model
{
    protected $table = 'fakultas_prodi';
    protected $guarded = [];

    public function fakultas() {
        return $this->belongsTo(Fakultas::class);
    }
}
