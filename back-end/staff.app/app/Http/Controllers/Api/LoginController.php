<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Void_;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        Log::info($request->all());

        $validate = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        Log::info($user);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Username salah'
            ], 401);
        }

        if (!$user && !Hash::check($request->password, $user->password)) {
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

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'Profile berhasil diambil'
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

            $validate = $request->validate([
                'name'      => 'required',
                'handphone' => 'required',
                'email'     => 'required',
                'level_id'  => 'required',
                'prodi_id'  => 'required',
            ]);

            $user           = User::find($id);
            $user->name     = $request->name;
            $user->username = $request->name;
            $user->prodi_id = $request->prodi_id;
            $user->phone    = $request->handphone;
            $user->email    = $request->email;
            $user->level_id = $request->level_id;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('img', 'public');
                $user->img = $path;
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
            $validate = $request->validate([
                'name'      => 'required',
                'handphone' => 'required',
                'email'     => 'required',
                'password'  => 'required',
                'prodi_id'  => 'required',
                'foto'      => 'required',
                'level_id'  => 'required',
            ]);

            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('img', 'public');
            }


            $user           = new User();
            $user->name     = $request->name;
            $user->username = $request->name;
            $user->phone    = $request->handphone;
            $user->email    = $request->email;
            $user->prodi_id = $request->prodi_id;
            $user->img      = $path;
            $user->password = Hash::make($request->password);
            $user->level_id = $request->level_id;
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
    public function getProdi() {
        $data = Prodi::all();
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => 'Data berhasil diambil'
        ]);
    }
}
