<?php

namespace Tests\Feature;

use App\Models\Fakultas;
use App\Models\FakultasProdi;
use App\Models\Level;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FakultasControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $level;
    private $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Create level and prodi dependencies
        $this->level = Level::create([
            'id' => 1,
            'nama' => 'staff'
        ]);

        $this->prodi = Prodi::create([
            'id' => 10,
            'nama' => 'Pendidikan Bahasa Arab',
            'alias' => 'PBA'
        ]);

        // Create acting user
        $this->user = User::create([
            'name' => 'prodipba',
            'username' => 'prodipba',
            'password' => Hash::make('secret123'),
            'level_id' => $this->level->id,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'email' => 'prodipba@example.com'
        ]);
    }

    /**
     * Test unauthenticated access returns 401
     */
    public function test_index_unauthenticated()
    {
        $response = $this->getJson('/api/fakultas');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated access to index and search
     */
    public function test_index_authenticated()
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Tarbiyah dan Keguruan',
            'kode' => 'FTK',
            'dekan' => 'Dr. H. Sulaiman',
            'nidn_dekan' => '2001010101'
        ]);

        FakultasProdi::create([
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $this->prodi->id
        ]);

        // 1. Basic authenticated listing
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/fakultas');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ])
            ->assertJsonStructure(['data']);

        // 2. Listing with search query
        $responseSearch = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/fakultas?search=Tarbiyah');

        $responseSearch->assertStatus(200);
        $this->assertCount(1, $responseSearch->json('data.data'));
    }

    /**
     * Test store new fakultas
     */
    public function test_store_fakultas()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/fakultas', [
                'kode_fakultas' => 'FTK_NEW',
                'nama_fakultas' => 'Fakultas Baru',
                'dekan' => 'Dekan Baru',
                'nidn_dekan' => '99999999',
                'prodi' => [
                    ['id' => $this->prodi->id]
                ]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('fakultas', [
            'kode' => 'FTK_NEW',
            'nama' => 'Fakultas Baru'
        ]);

        $this->assertDatabaseHas('fakultas_prodi', [
            'prodi_id' => $this->prodi->id
        ]);
    }

    /**
     * Test validation fails on store
     */
    public function test_store_validation_fails()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/fakultas', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    /**
     * Test show fakultas details
     */
    public function test_show_fakultas()
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Tarbiyah dan Keguruan',
            'kode' => 'FTK',
            'dekan' => 'Dr. H. Sulaiman',
            'nidn_dekan' => '2001010101'
        ]);

        FakultasProdi::create([
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $this->prodi->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/fakultas/{$fakultas->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ])
            ->assertJsonStructure(['data' => ['prodi']]);
    }

    /**
     * Test show nonexistent fakultas returns 404
     */
    public function test_show_nonexistent_fakultas()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/fakultas/99999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
    }

    /**
     * Test update fakultas and sync prodis
     */
    public function test_update_fakultas()
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Lama',
            'kode' => 'LAMA',
            'dekan' => 'Dekan Lama',
            'nidn_dekan' => '1111'
        ]);

        FakultasProdi::create([
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $this->prodi->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/fakultas/{$fakultas->id}", [
                'kode_fakultas' => 'BARU',
                'nama_fakultas' => 'Fakultas Diperbarui',
                'dekan' => 'Dekan Baru',
                'nidn_dekan' => '2222',
                'prodi' => [
                    ['id' => $this->prodi->id]
                ]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        $this->assertDatabaseHas('fakultas', [
            'id' => $fakultas->id,
            'nama' => 'Fakultas Diperbarui',
            'kode' => 'BARU'
        ]);
    }

    /**
     * Test destroy fakultas
     */
    public function test_destroy_fakultas()
    {
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Dihapus',
            'kode' => 'HAPUS',
            'dekan' => 'Dekan Hapus',
            'nidn_dekan' => '0000'
        ]);

        FakultasProdi::create([
            'fakultas_id' => $fakultas->id,
            'prodi_id' => $this->prodi->id
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/fakultas/{$fakultas->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('fakultas', [
            'id' => $fakultas->id
        ]);

        $this->assertDatabaseMissing('fakultas_prodi', [
            'fakultas_id' => $fakultas->id
        ]);
    }
}
