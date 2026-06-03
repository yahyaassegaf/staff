<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Level;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BatchControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $level;
    private $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Load nested ijazah and batch migrations
        $this->artisan('migrate', ['--path' => 'database/migrations/ijazah']);

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
     * Test unauthenticated access to batch index returns 401
     */
    public function test_index_unauthenticated()
    {
        $response = $this->getJson('/api/batch');

        $response->assertStatus(401);
    }

    /**
     * Test authenticated access to batch index
     */
    public function test_index_authenticated()
    {
        // Seed a batch
        Batch::create([
            'nama_batch' => 'Batch 2026-05-25',
            'tanggal_import' => '2026-05-25'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/batch');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Test store batch
     */
    public function test_store_batch()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/batch', [
                'nama_batch' => 'Import Batch Baru',
                'tanggal_import' => '2026-05-25'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('batch', [
            'nama_batch' => 'Import Batch Baru',
            'tanggal_import' => '2026-05-25'
        ]);
    }

    /**
     * Test show batch
     */
    public function test_show_batch()
    {
        $batch = Batch::create([
            'nama_batch' => 'Batch Specific',
            'tanggal_import' => '2026-05-25'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/batch/{$batch->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Test show nonexistent batch returns 404
     */
    public function test_show_nonexistent_batch()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/batch/99999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
    }

    /**
     * Test update batch
     */
    public function test_update_batch()
    {
        $batch = Batch::create([
            'nama_batch' => 'Old Name',
            'tanggal_import' => '2026-05-25'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/batch/{$batch->id}", [
                'nama_batch' => 'New Name Updated',
                'tanggal_import' => '2026-05-26'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        $this->assertDatabaseHas('batch', [
            'id' => $batch->id,
            'nama_batch' => 'New Name Updated',
            'tanggal_import' => '2026-05-26'
        ]);
    }

    /**
     * Test destroy batch
     */
    public function test_destroy_batch()
    {
        $batch = Batch::create([
            'nama_batch' => 'Delete Me',
            'tanggal_import' => '2026-05-25'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/batch/{$batch->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('batch', [
            'id' => $batch->id
        ]);
    }
}
