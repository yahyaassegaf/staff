<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\JenisSurat;

class JenisSuratControllerTest extends TestCase
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

        \Illuminate\Support\Facades\Schema::create('jenis_surat', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('alias')->nullable();
            $table->string('format_surat')->nullable();
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

    public function test_index_returns_paginated_jenis_surat()
    {
        JenisSurat::create([
            'nama' => 'Surat Keterangan Lulus',
            'alias' => 'SKL',
            'format_surat' => 'format-skl.pdf',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/jenis-surat');

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
                                 'nama',
                                 'alias',
                                 'format_surat',
                                 'created_at',
                                 'updated_at'
                             ]
                         ],
                         'total'
                     ],
                     'message'
                 ]);
    }

    public function test_index_can_search_jenis_surat()
    {
        JenisSurat::create([
            'nama' => 'Surat Keterangan Lulus',
            'alias' => 'SKL',
            'format_surat' => 'format-skl.pdf',
        ]);

        JenisSurat::create([
            'nama' => 'Surat Cuti',
            'alias' => 'SC',
            'format_surat' => 'format-cuti.pdf',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/jenis-surat?search=Cuti');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Surat Cuti', $data[0]['nama']);
    }

    public function test_store_creates_jenis_surat_with_valid_data()
    {
        $payload = [
            'nama' => 'Surat Tugas',
            'alias' => 'ST',
            'format_surat' => 'format-st.pdf',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/jenis-surat', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $this->assertDatabaseHas('jenis_surat', $payload);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama' => '', // Required
            'alias' => 'ST',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/jenis-surat', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['nama', 'format_surat']);
    }

    public function test_show_returns_jenis_surat()
    {
        $jenisSurat = JenisSurat::create([
            'nama' => 'Surat Keterangan Lulus',
            'alias' => 'SKL',
            'format_surat' => 'format-skl.pdf',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/jenis-surat/' . $jenisSurat->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'data' => [
                         'id' => $jenisSurat->id,
                         'nama' => 'Surat Keterangan Lulus',
                         'alias' => 'SKL',
                     ],
                     'message' => 'Data berhasil diambil'
                 ]);
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/jenis-surat/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_jenis_surat_with_valid_data()
    {
        $jenisSurat = JenisSurat::create([
            'nama' => 'Surat Keterangan Lulus',
            'alias' => 'SKL',
            'format_surat' => 'format-skl.pdf',
        ]);

        $payload = [
            'nama' => 'Surat Keterangan Lulus Update',
            'alias' => 'SKL-UPD',
            'format_surat' => 'format-skl-upd.pdf',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/jenis-surat/' . $jenisSurat->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        $this->assertDatabaseHas('jenis_surat', $payload);
    }

    public function test_update_fails_with_invalid_data()
    {
        $jenisSurat = JenisSurat::create([
            'nama' => 'Surat Keterangan Lulus',
            'alias' => 'SKL',
            'format_surat' => 'format-skl.pdf',
        ]);

        $payload = [
            'nama' => '', // Required field missing
            'alias' => 'SKL-UPD',
            'format_surat' => 'format-skl-upd.pdf',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/jenis-surat/' . $jenisSurat->id, $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['nama']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'nama' => 'Surat Keterangan Lulus Update',
            'alias' => 'SKL-UPD',
            'format_surat' => 'format-skl-upd.pdf',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/jenis-surat/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_jenis_surat()
    {
        $jenisSurat = JenisSurat::create([
            'nama' => 'Surat Keterangan Lulus',
            'alias' => 'SKL',
            'format_surat' => 'format-skl.pdf',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/jenis-surat/' . $jenisSurat->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('jenis_surat', ['id' => $jenisSurat->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/jenis-surat/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
