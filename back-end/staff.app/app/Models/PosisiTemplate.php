<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosisiTemplate extends Model
{
    protected $table = 'posisi_template';

    protected $guarded = [];

    public function templateIjazah()
    {
        return $this->belongsTo(TemplateIjazah::class, 'template_id');
    }
}
