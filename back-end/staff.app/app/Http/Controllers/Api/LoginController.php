<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Void_;

class LoginController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $validate = $validator->validated();

        $user = User::join('level', 'level.id', '=', 'users.level_id')
            ->leftJoin('prodi', 'prodi.id', '=', 'users.prodi_id')
            ->where('username', $validate['username'])
            ->select('users.*', 'level.nama as level', 'prodi.nama as prodi', 'prodi.alias as prodi_alias')
            ->first();

        Log::info($user);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Username salah'
            ], 401);
        }

        if (!Hash::check($validate['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        // Ambil data user beserta nama level
        $userWithLevel = User::join('level', 'level.id', '=', 'users.level_id')
            ->leftJoin('prodi', 'prodi.id', '=', 'users.prodi_id')
            ->where('users.id', $user->id)
            ->select('users.*', 'level.nama as level')
            ->first();

        return response()->json([
            'status' => true,
            'user' => $userWithLevel,
            'message' => 'Profile berhasil diambil'
        ]);
    }

    public function updateProfile(Request $request)
    {
        Log::info($request->all());
        try {

            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name'      => 'required',
                'username'  => 'required|unique:users,username,' . $user->id,
                'handphone' => 'required',
                'email'     => 'required|email|unique:users,email,' . $user->id,

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $user->name     = $validate['name'];
            $user->username = $validate['username'];
            $user->phone    = $validate['handphone'];
            $user->email    = $validate['email'];

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('foto')) {
                $dir = base_path('../public_html/foto/');
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file = $request->file('foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($dir, $filename);
                $user->img = 'foto/' . $filename;
            }

            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Profile berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status'  => false,
                'message' => 'Profile gagal diupdate: ' . $th->getMessage()
            ]);
        }
    }

    public function serveFoto($filename)
    {
        $path = base_path('../public_html/foto/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path);
        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    public function dataUsers(Request $request)
    {
        Log::info($request->all());

        $data = User::query();

        $data->select('users.*', 'level.nama as level');

        $data->join('level', 'level.id', '=', 'users.level_id');

        if ($request->filled('search')) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->orWhere('name', 'LIKE', "%{$search}%");
                $q->orWhere('level.nama', 'LIKE', "%{$search}%");
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

    public function show($id)
    {
        // Log::info($id);
        $user = User::find($id);
        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function getLevel()
    {
        try {
            $level = Level::all();
            return response()->json([
                'status' => true,
                'data' => $level
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Data gagal diambil'
                ]
            );
        }
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        try {

            $validator = Validator::make($request->all(), [
                'name'      => 'required',
                'handphone' => 'required',
                'email'     => 'required',
                'level_id'  => 'required',
                'prodi_id'  => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $user           = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $user->name = $validate['name'];
            $user->username = $validate['name'];
            $user->prodi_id = $validate['prodi_id'];
            $user->phone = $validate['handphone'];
            $user->email = $validate['email'];
            $user->level_id = $validate['level_id'];
            $user->jenis_kelamin = $request->jenis_kelamin;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('foto')) {
                $dir = base_path('../public_html/foto/');
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file = $request->file('foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($dir, $filename);
                $user->img = 'foto/' . $filename;
            }

            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status'  => false,
                'message' => 'Data gagal diupdate'
            ]);
        }
    }
    public function destroy($id)
    {
        try {
            $data = User::find($id);
            $data->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status'  => false,
                'message' => 'Data gagal dihapus'
            ]);
        }
    }

    public function store(Request $request)
    {
        Log::info($request->all());
        try {
            $validator = Validator::make($request->all(), [
                'name'      => 'required',
                'handphone' => 'required',
                'email'     => 'required',
                'password'  => 'required',
                'prodi_id'  => 'required',
                'foto'      => 'required',
                'level_id'  => 'required',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validate = $validator->validated();

            $path = null;
            if ($request->hasFile('foto')) {
                $dir = base_path('../public_html/foto/');
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file = $request->file('foto');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($dir, $filename);
                $path = 'foto/' . $filename;
            }


            $user = new User();
            $user->name = $validate['name'];
            $user->username = $validate['name'];
            $user->phone = $validate['handphone'];
            $user->email = $validate['email'];
            $user->prodi_id = $validate['prodi_id'];
            $user->img = $path;
            $user->password = Hash::make($validate['password']);
            $user->level_id = $validate['level_id'];
            $user->jenis_kelamin = $request->jenis_kelamin;
            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Data berhasil ditambahkan'
            ]);
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json([
                'status'  => false,
                'message' => 'Data gagal ditambahkan'
            ]);
        }
    }
    public function getProdi()
    {
        $data = Prodi::all();
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }

    public function getTandaTangan()
    {
        try {
            $data = \App\Models\TandaTangan::all();
            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil'
            ]);
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Data gagal diambil'
                ]
            );
        }
    }
}
