<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Predikat extends Model
{
    protected $table = 'predikat';
    protected $guarded = [];

    /**
     * Get predikat by IPK
     * 
     * @param float|string $ipk
     * @return string|null
     */
    public static function getPredikat($ipk)
    {
        $ipk = (float) $ipk;
        $predikat = self::where('nilai_min', '<=', $ipk)
            ->where('nilai_max', '>=', $ipk)
            ->first();

        return $predikat ? $predikat->nama : null;
    }
}
