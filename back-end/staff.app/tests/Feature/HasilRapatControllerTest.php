<?php

namespace Tests\Feature;

use App\Models\HasilRapat;
use App\Models\AnggotaRapat;
use App\Models\JenisSurat;
use App\Models\Level;
use App\Models\LogSurat;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HasilRapatControllerTest extends TestCase
{
    use DatabaseMigrations;

    private $staffLevel;
    private $adminLevel;
    private $prodiPba;
    private $prodiEnglish;
    private $staffUser;
    private $adminUser;
    private $memberUser1;
    private $memberUser2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Fake Queue and Google Drive Storage to prevent external API calls
        Queue::fake();
        Storage::fake('google');

        // 2. Setup Levels
        $this->staffLevel = Level::create([
            'id' => 1,
            'nama' => 'staff'
        ]);

        $this->adminLevel = Level::create([
            'id' => 2,
            'nama' => 'admin'
        ]);

        // 3. Setup Prodis
        $this->prodiPba = Prodi::create([
            'id' => 10,
            'nama' => 'Pendidikan Bahasa Arab',
            'alias' => 'PBA'
        ]);

        $this->prodiEnglish = Prodi::create([
            'id' => 11,
            'nama' => 'Pendidikan Bahasa Inggris',
            'alias' => 'PBI'
        ]);

        // 4. Setup Users
        $this->staffUser = User::create([
            'id' => 100,
            'name' => 'Staff PBA',
            'username' => 'staffpba',
            'password' => Hash::make('secret123'),
            'level_id' => $this->staffLevel->id,
            'prodi_id' => $this->prodiPba->id,
            'jenis_kelamin' => 'L',
            'email' => 'staffpba@example.com'
        ]);

        $this->adminUser = User::create([
            'id' => 101,
            'name' => 'Admin System',
            'username' => 'admin',
            'password' => Hash::make('secret123'),
            'level_id' => $this->adminLevel->id,
            'prodi_id' => null,
            'jenis_kelamin' => 'L',
            'email' => 'admin@example.com'
        ]);

        $this->memberUser1 = User::create([
            'id' => 102,
            'name' => 'Dosen PBA 1',
            'username' => 'dosenpba1',
            'password' => Hash::make('secret123'),
            'level_id' => $this->staffLevel->id,
            'prodi_id' => $this->prodiPba->id,
            'jenis_kelamin' => 'L',
            'email' => 'dosenpba1@example.com'
        ]);

        $this->memberUser2 = User::create([
            'id' => 103,
            'name' => 'Dosen PBA 2',
            'username' => 'dosenpba2',
            'password' => Hash::make('secret123'),
            'level_id' => $this->staffLevel->id,
            'prodi_id' => $this->prodiPba->id,
            'jenis_kelamin' => 'P',
            'email' => 'dosenpba2@example.com'
        ]);

        // 5. Setup JenisSurat untuk STHR
        if (!\Illuminate\Support\Facades\Schema::hasTable('jenis_surat')) {
            \Illuminate\Support\Facades\Schema::create('jenis_surat', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('alias', 255);
                $table->string('format_surat', 255);
                $table->string('nama', 255)->nullable();
                $table->timestamps();
            });
        }

        JenisSurat::create([
            'alias' => 'STHR',
            'format_surat' => 'SU-{NO}/UII.085/K.{PRODI}/PP.00/{BULAN}/{TAHUN}',
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up any generated PDF files to keep the local filesystem clean
        $directory = base_path('../public_html/pdf/');
        if (is_dir($directory)) {
            $files = glob($directory . 'hasil_rapat_*.pdf');
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        parent::tearDown();
    }

    /**
     * Test unauthenticated access returns 401
     */
    public function test_index_unauthenticated()
    {
        $response = $this->getJson('/api/hasil-rapat');
        $response->assertStatus(401);
    }

    /**
     * Test staff can only see hasil rapat from their own prodi
     */
    public function test_index_staff_filtered()
    {
        // Rapat for PBA (staff's prodi)
        HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Agenda PBA',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        // Rapat for PBI (other prodi)
        HasilRapat::create([
            'nomor_surat' => 'SU-002/UII.085/K.PBI/PP.00/05/2026',
            'prodi_id' => $this->prodiEnglish->id,
            'agenda' => 'Agenda PBI',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->getJson('/api/hasil-rapat');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals($this->prodiPba->id, $data[0]['prodi_id']);
    }

    /**
     * Test admin can see hasil rapat from all prodis
     */
    public function test_index_admin_unfiltered()
    {
        // Rapat for PBA
        HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Agenda PBA',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        // Rapat for PBI
        HasilRapat::create([
            'nomor_surat' => 'SU-002/UII.085/K.PBI/PP.00/05/2026',
            'prodi_id' => $this->prodiEnglish->id,
            'agenda' => 'Agenda PBI',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/hasil-rapat');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    /**
     * Test index search functionality
     */
    public function test_index_search()
    {
        HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Evaluasi Kurikulum PBA',
            'tempat' => 'Ruang Sidang',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        HasilRapat::create([
            'nomor_surat' => 'SU-002/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Sosialisasi Akademik',
            'tempat' => 'Aula Utama',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        // 1. Search by agenda
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->getJson('/api/hasil-rapat?search=Kurikulum');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
        $this->assertEquals('Evaluasi Kurikulum PBA', $response->json('data.data.0.agenda'));

        // 2. Search by tempat
        $response2 = $this->actingAs($this->staffUser, 'sanctum')
            ->getJson('/api/hasil-rapat?search=Aula');
        $response2->assertStatus(200);
        $this->assertCount(1, $response2->json('data.data'));
        $this->assertEquals('Sosialisasi Akademik', $response2->json('data.data.0.agenda'));
    }

    /**
     * Test store hasil rapat successfully with no_surat
     */
    public function test_store_hasil_rapat_with_no_surat()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/hasil-rapat', [
                'no_surat' => '001',
                'prodi_id' => $this->prodiPba->id,
                'agenda' => 'Rapat Rencana Strategis PBA',
                'tanggal' => '2026-05-25',
                'waktu' => '10:00',
                'tempat' => 'Meeting Room A',
                'pembahasan' => '<p>Pembahasan akreditasi dan target kelulusan.</p>',
                'anggota_ids' => [
                    $this->memberUser1->id,
                    $this->memberUser2->id
                ]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);

        // Assert Hasil Rapat record created in database
        $this->assertDatabaseHas('hasil_rapat', [
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Rapat Rencana Strategis PBA',
            'status' => 'pending'
        ]);

        $hasilRapat = HasilRapat::where('agenda', 'Rapat Rencana Strategis PBA')->first();

        // Assert nomor_surat is formatted using SuratService::formatNomorSurat('STHR', ...)
        $expectedNomor = \App\Services\SuratService::formatNomorSurat('STHR', '001', '2026-05-25', $this->prodiPba->id);
        $this->assertEquals($expectedNomor, $hasilRapat->nomor_surat);

        // Assert Anggota Rapat entries
        $this->assertDatabaseHas('anggota_rapat', [
            'hasil_rapat_id' => $hasilRapat->id,
            'user_id' => $this->memberUser1->id
        ]);
        $this->assertDatabaseHas('anggota_rapat', [
            'hasil_rapat_id' => $hasilRapat->id,
            'user_id' => $this->memberUser2->id
        ]);
    }

    /**
     * Test store hasil rapat without no_surat (nomor_surat should be null)
     */
    public function test_store_hasil_rapat_without_no_surat()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/hasil-rapat', [
                'prodi_id' => $this->prodiPba->id,
                'agenda' => 'Rapat Tanpa Nomor Surat',
                'tanggal' => '2026-05-25',
                'waktu' => '10:00',
                'tempat' => 'Meeting Room B',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);

        $hasilRapat = HasilRapat::where('agenda', 'Rapat Tanpa Nomor Surat')->first();
        $this->assertNull($hasilRapat->nomor_surat);
    }

    /**
     * Test validation failure on store
     */
    public function test_store_validation_fails()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->postJson('/api/hasil-rapat', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validasi gagal'
            ])
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test show single hasil rapat details
     */
    public function test_show_hasil_rapat()
    {
        $hasil = HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Agenda Rapat Show',
            'tanggal' => '2026-05-25',
            'tempat' => 'Ruang Prodi',
            'status' => 'pending'
        ]);

        AnggotaRapat::create([
            'hasil_rapat_id' => $hasil->id,
            'user_id' => $this->memberUser1->id
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->getJson("/api/hasil-rapat/{$hasil->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'id' => $hasil->id,
                    'agenda' => 'Agenda Rapat Show'
                ]
            ])
            ->assertJsonStructure([
                'data' => [
                    'prodi',
                    'anggota' => [
                        '*' => ['user']
                    ]
                ]
            ]);
    }

    /**
     * Test show nonexistent hasil rapat returns 404
     */
    public function test_show_nonexistent_hasil_rapat()
    {
        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->getJson('/api/hasil-rapat/99999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
    }

    /**
     * Test update hasil rapat with no_surat
     */
    public function test_update_hasil_rapat()
    {
        $hasil = HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Agenda Rapat Lama',
            'tanggal' => '2026-05-25',
            'status' => 'pending',
            'drive_file_id' => 'old_google_drive_file_id_123'
        ]);

        // Old members
        AnggotaRapat::create([
            'hasil_rapat_id' => $hasil->id,
            'user_id' => $this->memberUser1->id
        ]);
        AnggotaRapat::create([
            'hasil_rapat_id' => $hasil->id,
            'user_id' => $this->memberUser2->id
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->putJson("/api/hasil-rapat/{$hasil->id}", [
                'no_surat' => '002',
                'agenda' => 'Agenda Rapat Baru Update',
                'tanggal' => '2026-05-26',
                'waktu' => '13:00',
                'tempat' => 'Ruang Dekanat',
                'pembahasan' => '<p>Pembaruan pembahasan rapat.</p>',
                'anggota_ids' => [
                    $this->memberUser1->id // user 2 deleted, only user 1 remains
                ]
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        // Assert fields updated
        $updatedHasil = HasilRapat::find($hasil->id);

        $this->assertEquals('Agenda Rapat Baru Update', $updatedHasil->agenda);
        $this->assertEquals('2026-05-26', $updatedHasil->tanggal);
        $this->assertEquals('13:00', $updatedHasil->waktu);
        $this->assertEquals('pending', $updatedHasil->status);
        $this->assertNull($updatedHasil->drive_file_id);

        // Assert nomor_surat is re-formatted
        $expectedNomor = \App\Services\SuratService::formatNomorSurat('STHR', '002', '2026-05-26', $this->prodiPba->id);
        $this->assertEquals($expectedNomor, $updatedHasil->nomor_surat);

        // Assert memberUser2 is removed and memberUser1 is synced
        $this->assertDatabaseHas('anggota_rapat', [
            'hasil_rapat_id' => $hasil->id,
            'user_id' => $this->memberUser1->id
        ]);
        $this->assertDatabaseMissing('anggota_rapat', [
            'hasil_rapat_id' => $hasil->id,
            'user_id' => $this->memberUser2->id
        ]);

        // Assert local_path is set in the database
        $this->assertNotNull($updatedHasil->local_path);
        $this->assertFileExists($updatedHasil->local_path);

        // Assert dispatching of UploudSuratToDrive job
        Queue::assertPushed(\App\Jobs\UploudSuratToDrive::class);
    }

    /**
     * Test update hasil rapat without no_surat keeps existing nomor_surat
     */
    public function test_update_hasil_rapat_without_no_surat_keeps_existing()
    {
        $hasil = HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Agenda Rapat Lama',
            'tanggal' => '2026-05-25',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->putJson("/api/hasil-rapat/{$hasil->id}", [
                'agenda' => 'Agenda Rapat Update Tanpa No Surat',
                'tanggal' => '2026-05-26',
            ]);

        $response->assertStatus(200);

        // nomor_surat should remain unchanged
        $updatedHasil = HasilRapat::find($hasil->id);
        $this->assertEquals('SU-001/UII.085/K.PBA/PP.00/05/2026', $updatedHasil->nomor_surat);
    }

    /**
     * Test destroy hasil rapat
     */
    public function test_destroy_hasil_rapat()
    {
        $hasil = HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Rapat Dihapus',
            'tanggal' => '2026-05-25',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')
            ->deleteJson("/api/hasil-rapat/{$hasil->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('hasil_rapat', [
            'id' => $hasil->id
        ]);
    }

    /**
     * Test downloading generated PDF
     */
    public function test_download_pdf()
    {
        // Buat file dummy
        $dummyPath = base_path('../public_html/pdf/hasil_rapat_dummy_' . time() . '.pdf');
        if (!is_dir(dirname($dummyPath))) {
            mkdir(dirname($dummyPath), 0777, true);
        }
        file_put_contents($dummyPath, 'dummy pdf content');

        $hasil = HasilRapat::create([
            'nomor_surat' => 'SU-001/UII.085/K.PBA/PP.00/05/2026',
            'prodi_id' => $this->prodiPba->id,
            'agenda' => 'Rapat Unduh PDF',
            'tanggal' => '2026-05-25',
            'status' => 'pending',
            'local_path' => $dummyPath
        ]);

        $response = $this->actingAs($this->staffUser, 'sanctum')->getJson("/api/hasil-rapat/download-pdf/{$hasil->id}");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');

        $updatedHasil = HasilRapat::find($hasil->id);
        $this->assertNotNull($updatedHasil->local_path);
        $this->assertFileExists($updatedHasil->local_path);
    }

    /**
     * Test getProdi method directly for staff user
     */
    public function test_get_prodi_method_directly_staff()
    {
        $this->actingAs($this->staffUser);
        $controller = new \App\Http\Controllers\Api\HasilRapatController();
        $response = $controller->getProdi();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertTrue($data['status']);
        $this->assertCount(1, $data['data']);
        $this->assertEquals($this->prodiPba->id, $data['data'][0]['id']);
    }

    /**
     * Test getProdi method directly for admin user (who has no prodi)
     */
    public function test_get_prodi_method_directly_admin()
    {
        $this->actingAs($this->adminUser);
        $controller = new \App\Http\Controllers\Api\HasilRapatController();
        $response = $controller->getProdi();

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);

        $this->assertTrue($data['status']);
        // Since admin has null prodi_id, it returns all prodis (we registered PBI & PBA, total 2)
        $this->assertCount(2, $data['data']);
    }
}
