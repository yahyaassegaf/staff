<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    protected $table = 'prodi';

    protected $guarded = [];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function tandaTangan()
    {
        return $this->belongsTo(TandaTangan::class, 'tanda_tangan_id');
    }
}
