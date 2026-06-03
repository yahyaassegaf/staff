<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\SettingJabatan;
use App\Models\TandaTangan;

class SettingJabatanControllerTest extends TestCase
{
    protected $user;

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
            $table->text('tdd')->nullable();
            $table->string('gambar')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('setting_jabatan', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('kunci_jabatan')->unique();
            $table->string('nama_jabatan');
            $table->string('nidn')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('level')->insert([
            'id' => 1,
            'nama' => 'Test Level',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->user = User::factory()->create(['level_id' => 1]);
    }

    public function test_index_returns_paginated_setting_jabatan()
    {
        $tandaTangan = TandaTangan::create([
            'nama' => 'Prof. Tester',
            'user_id' => $this->user->id,
        ]);

        SettingJabatan::create([
            'kunci_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor Universitas',
            'nidn' => '12345678',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/setting-jabatan');

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
                                 'kunci_jabatan',
                                 'nama_jabatan',
                                 'tanda_tangan' => [
                                     'id',
                                     'nama'
                                 ]
                             ]
                         ],
                         'total'
                     ],
                     'message'
                 ]);
    }

    public function test_index_can_search_setting_jabatan()
    {
        $tandaTangan = TandaTangan::create([
            'nama' => 'Prof. Tester',
            'user_id' => $this->user->id,
        ]);

        SettingJabatan::create([
            'kunci_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor Universitas',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        SettingJabatan::create([
            'kunci_jabatan' => 'DEKAN',
            'nama_jabatan' => 'Dekan Fakultas',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/setting-jabatan?search=DEKAN');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('DEKAN', $data[0]['kunci_jabatan']);
    }

    public function test_store_creates_setting_jabatan_and_tanda_tangan()
    {
        $payload = [
            'kunci_jabatan' => 'WAREK1',
            'nama_jabatan' => 'Wakil Rektor 1',
            'nidn' => '11223344',
            'nama_tanda_tangan' => 'Dr. Wakil',
            'tdd' => 'Tanda Tangan Elektronik WAREK 1'
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/setting-jabatan', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $this->assertDatabaseHas('setting_jabatan', [
            'kunci_jabatan' => 'WAREK1',
            'nama_jabatan' => 'Wakil Rektor 1',
            'nidn' => '11223344',
        ]);

        $this->assertDatabaseHas('tanda_tangan', [
            'nama' => 'Dr. Wakil',
            'tdd' => 'Tanda Tangan Elektronik WAREK 1',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_store_fails_with_duplicate_kunci_jabatan()
    {
        $tandaTangan = TandaTangan::create([
            'nama' => 'Prof. Tester',
            'user_id' => $this->user->id,
        ]);

        SettingJabatan::create([
            'kunci_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor Universitas',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        $payload = [
            'kunci_jabatan' => 'REKTOR', // Duplicate
            'nama_jabatan' => 'Rektor 2',
            'nama_tanda_tangan' => 'Dr. Wakil',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/setting-jabatan', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['kunci_jabatan']);
    }

    public function test_show_returns_setting_jabatan()
    {
        $tandaTangan = TandaTangan::create([
            'nama' => 'Prof. Tester',
            'user_id' => $this->user->id,
        ]);

        $setting = SettingJabatan::create([
            'kunci_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor Universitas',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/setting-jabatan/' . $setting->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'id' => $setting->id,
                         'kunci_jabatan' => 'REKTOR',
                         'tanda_tangan' => [
                             'id' => $tandaTangan->id,
                             'nama' => 'Prof. Tester'
                         ]
                     ]
                 ]);
    }

    public function test_show_returns_404()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/setting-jabatan/999');
        $response->assertStatus(404);
    }

    public function test_update_modifies_setting_jabatan_and_tanda_tangan()
    {
        $tandaTangan = TandaTangan::create([
            'nama' => 'Prof. Tester',
            'tdd' => 'Old Signature',
            'user_id' => $this->user->id,
        ]);

        $setting = SettingJabatan::create([
            'kunci_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor Universitas',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        $payload = [
            'kunci_jabatan' => 'REKTOR_UPDATED',
            'nama_jabatan' => 'Rektor Universitas Updated',
            'nidn' => '998877',
            'nama_tanda_tangan' => 'Prof. Tester Updated',
            'tdd' => 'New Signature'
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/setting-jabatan/' . $setting->id, $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('setting_jabatan', [
            'id' => $setting->id,
            'kunci_jabatan' => 'REKTOR_UPDATED',
            'nama_jabatan' => 'Rektor Universitas Updated',
            'nidn' => '998877',
        ]);

        $this->assertDatabaseHas('tanda_tangan', [
            'id' => $tandaTangan->id,
            'nama' => 'Prof. Tester Updated',
            'tdd' => 'New Signature',
        ]);
    }

    public function test_destroy_deletes_setting_jabatan_and_tanda_tangan()
    {
        $tandaTangan = TandaTangan::create([
            'nama' => 'Prof. Tester',
            'user_id' => $this->user->id,
        ]);

        $setting = SettingJabatan::create([
            'kunci_jabatan' => 'REKTOR',
            'nama_jabatan' => 'Rektor Universitas',
            'tanda_tangan_id' => $tandaTangan->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/setting-jabatan/' . $setting->id);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('setting_jabatan', ['id' => $setting->id]);
        $this->assertDatabaseMissing('tanda_tangan', ['id' => $tandaTangan->id]);
    }
}
