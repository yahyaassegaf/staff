<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;

class ProdiControllerTest extends TestCase
{
    protected $user;
    protected $tandaTanganId;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Schema::create('level', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('users', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('level_id');
            $table->rememberToken();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('tanda_tangan', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('prodi', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('kode')->nullable();
            $table->string('alias')->nullable();
            $table->string('nama')->nullable();
            $table->enum('aktif', ['Y', 'T'])->default('T');
            $table->enum('jenjang', ['S1', 'S2', 'S3'])->default('S1');
            $table->string('nidn_kepala')->nullable();
            $table->string('nama_kepala')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('level')->insert([
            'id' => 1,
            'nama' => 'Test Level',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\DB::table('tanda_tangan')->insert([
            'id' => 1,
            'nama' => 'Prof. Dr. Tester',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->tandaTanganId = 1;

        $this->user = User::factory()->create(['level_id' => 1]);
    }

    public function test_index_returns_paginated_prodi()
    {
        Prodi::create([
            'kode' => 'TI',
            'alias' => 'IT',
            'nama' => 'Teknik Informatika',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/prodi');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ])
                 ->assertJsonStructure([
                     'status',
                     'data' => [
                         'current_page',
                         'data' => [
                             '*' => [
                                 'id',
                                 'kode',
                                 'alias',
                                 'nama',
                                 'aktif',
                             ]
                         ],
                         'total'
                     ],
                     'message'
                 ]);
    }

    public function test_index_can_search_prodi()
    {
        Prodi::create([
            'kode' => 'TI',
            'alias' => 'IT',
            'nama' => 'Teknik Informatika',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
        ]);

        Prodi::create([
            'kode' => 'SI',
            'alias' => 'IS',
            'nama' => 'Sistem Informasi',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/prodi?search=Informatika');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Teknik Informatika', $data[0]['nama']);
    }

    public function test_store_creates_prodi_with_valid_data()
    {
        $payload = [
            'kode' => 'TE',
            'alias' => 'EE',
            'nama' => 'Teknik Elektro',
            'aktif' => 'Y',
            'jenjang' => 'S1',
            'tanda_tangan' => $this->tandaTanganId,
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/prodi', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $this->assertDatabaseHas('prodi', [
            'kode' => 'TE',
            'nama' => 'Teknik Elektro',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTanganId,
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama' => 'Prodi Tanpa Aktif',
            // 'aktif' is required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/prodi', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['aktif']);
    }

    public function test_show_returns_prodi_with_joined_tanda_tangan()
    {
        $prodi = Prodi::create([
            'kode' => 'TI',
            'alias' => 'IT',
            'nama' => 'Teknik Informatika',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTanganId,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/prodi/' . $prodi->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'id' => $prodi->id,
                         'nama' => 'Teknik Informatika',
                         'tanda_tangan' => 'Prof. Dr. Tester', // From the join
                     ],
                     'message' => 'Data berhasil diambil'
                 ]);
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/prodi/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_prodi_with_valid_data()
    {
        $prodi = Prodi::create([
            'kode' => 'TI',
            'alias' => 'IT',
            'nama' => 'Teknik Informatika',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
        ]);

        $payload = [
            'kode' => 'TI-UPD',
            'nama' => 'Teknik Informatika Update',
            'aktif' => 'T',
            'jenjang' => 'S2',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/prodi/' . $prodi->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        $this->assertDatabaseHas('prodi', [
            'id' => $prodi->id,
            'kode' => 'TI-UPD',
            'nama' => 'Teknik Informatika Update',
            'aktif' => 'T',
            'jenjang' => 'S2',
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $prodi = Prodi::create([
            'kode' => 'TI',
            'alias' => 'IT',
            'nama' => 'Teknik Informatika',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
        ]);

        $payload = [
            'nama' => 'Teknik Informatika Update',
            // 'aktif' is required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/prodi/' . $prodi->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['aktif']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'nama' => 'Teknik Update',
            'aktif' => 'Y',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/prodi/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_prodi()
    {
        $prodi = Prodi::create([
            'kode' => 'TI',
            'alias' => 'IT',
            'nama' => 'Teknik Informatika',
            'aktif' => 'Y',
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/prodi/' . $prodi->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('prodi', ['id' => $prodi->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/prodi/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
