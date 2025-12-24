<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\FakultasProdi;
use App\Models\Fakultas;
use App\Models\Prodi;

class FakultasController extends Controller
{
    public function index(Request $request)
    {
          $data = FakultasProdi::join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id');
            

        $data->select('fakultas_prodi.id as id','fakultas_prodi.prodi_id',
        'fakultas_prodi.fakultas_id','fakultas_prodi.id as id',
        'fakultas.nama as nama_fakultas','fakultas.dekan as dekan_fakultas',
        'prodi.nama_kepala as nama_kepala_prodi','prodi.nama as nama_prodi',
        'prodi.alias as alias_prodi');

        if ($request->filled('search')) {

            $search = $request->search;

            $data->where(function ($q) use ($search) {
                $q->orWhere('dekan_fakultas', 'LIKE', "%{$search}%");
                $q->orWhere('nama_kepala_prodi', 'LIKE', "%{$search}%");
                $q->orWhere('nama_fakultas', 'LIKE', "%{$search}%");
                $q->orWhere('nama_prodi', 'LIKE', "%{$search}%");
                $q->orWhere('alias_prodi', 'LIKE', "%{$search}%");
            });
        }

        $data->orderBy(
            $request->input('sortBy', 'id'),
            $request->input('sortType', 'asc')
        );

        $data = $data->paginate($request->input('limit', 10));

        return response()->json(
            [
                'status' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil'
            ]
        );
    }

    public function store(Request $request)
    {
        Log::info($request->all());
       try {
            $validate = $request->validate([
                'kode_fakultas'=>'required',
                'nama_fakultas'=>'required',
                'alias'        =>'required',
                'kode'         =>'required',
                'nama'         =>'required',
                'dekan'        =>'required',
                'jenjang'      =>'required',
                'nidn_kepala'  =>'required',
                'nama_kepala'  =>'required',
            ]);

            $fakultas           = new Fakultas();
            $fakultas->nama     = $request->nama_fakultas;
            $fakultas->kode     = $request->kode_fakultas;
            $fakultas->dekan    = $request->dekan;
            $fakultas->save();

            $prodi              = new Prodi();
            $prodi->kode        = $request->kode;
            $prodi->nama        = $request->nama;
            $prodi->alias       = $request->alias;
            $prodi->jenjang     = $request->jenjang;
            $prodi->nidn_kepala = $request->nidn_kepala;
            $prodi->nama_kepala = $request->nama_kepala;
            $prodi->save();
            
            $fakultasProdi              = new FakultasProdi();
            $fakultasProdi->fakultas_id = $fakultas->id;
            $fakultasProdi->prodi_id    = $prodi->id;
            $fakultasProdi->save();

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
       } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal ditambahkan'
            ]);
       }
        
    }
        public function show($id)
    {
        // Log::info('id fakultas_prodi ',$id);
        $user = FakultasProdi::join('fakultas', 'fakultas.id', '=', 'fakultas_prodi.fakultas_id')
            ->join('prodi', 'prodi.id', '=', 'fakultas_prodi.prodi_id')
            ->select('fakultas_prodi.*',
            'prodi.nama as nama',
            'prodi.alias as alias',
            'prodi.kode as kode',
            'prodi.jenjang as jenjang',
            'prodi.nidn_kepala as nidn_kepala',
            'prodi.nama_kepala as nama_kepala',
            'fakultas.nama as nama_fakultas','fakultas.kode as kode_fakultas','fakultas.dekan as dekan',)
            ->where('fakultas_prodi.id', $id)
            ->first();
        return response()->json([
            'status' => true,
            'data' => $user,
            'message' => 'Data berhasil diambil'
        ]);
    }
    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {
            $validate = $request->validate([
                'kode_fakultas'=>'required',
                'nama_fakultas'=>'required',
                'alias'        =>'required',
                'kode'         =>'required',
                'nama'         =>'required',
                'dekan'        =>'required',
                'jenjang'      =>'required',
                'nidn_kepala'  =>'required',
                'nama_kepala'  =>'required',
            ]);

            $fakultasProdi               = FakultasProdi::find($id);
            
            $fakultas                    = Fakultas::find($fakultasProdi->fakultas_id);
            $fakultas->nama              = $request->nama_fakultas;
            $fakultas->kode              = $request->kode_fakultas;
            $fakultas->dekan             = $request->dekan;
            $fakultas->save();

            $prodi                       = Prodi::find($fakultasProdi->prodi_id);
            $prodi->nama                 = $request->nama;
            $prodi->kode                 = $request->kode;
            $prodi->alias                = $request->alias;
            $prodi->jenjang              = $request->jenjang;
            $prodi->nidn_kepala          = $request->nidn_kepala;
            $prodi->nama_kepala          = $request->nama_kepala;
            $prodi->save();

            

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);
            
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }
    public function destroy($id) {
        try {
            $fakultasProdi = FakultasProdi::find($id);
            $fakultas = Fakultas::find($fakultasProdi->fakultas_id);
            $prodi = Prodi::find($fakultasProdi->prodi_id);
            $fakultas->delete();
            $prodi->delete();
            $fakultasProdi->delete();
            return response()->json([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status' => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }
}
