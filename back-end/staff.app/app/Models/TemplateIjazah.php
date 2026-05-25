<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateIjazah extends Model
{
    protected $table = 'template_ijazah';

    protected $guarded = [];

    protected $casts = [
        'prodi_id' => 'integer',
        'user_id' => 'integer',
        'fields_positions' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}
