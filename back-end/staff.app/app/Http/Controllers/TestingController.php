<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function index(Request $request)
    {
        $data = \App\Services\Mahasiswa::all(
            null,   // offset
            20,     // limit
            '201785020617',
            null,   // order
            null,   // dir
            [
                ['mst_prodi.alias', '=', 'PBA']
            ], // where
            null    // pluck
        );
        dd($data);
    }
}
