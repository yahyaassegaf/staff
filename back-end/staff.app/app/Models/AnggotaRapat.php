<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaRapat extends Model
{
    use HasFactory;

    protected $table = 'anggota_rapat';

    protected $fillable = [
        'hasil_rapat_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with HasilRapat (assuming model will be created)
    public function hasilRapat(): BelongsTo
    {
        return $this->belongsTo(HasilRapat::class);
    }
}
