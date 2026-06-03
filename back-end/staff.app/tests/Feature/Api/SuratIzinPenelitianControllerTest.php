<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratIzinPenelitian;
use App\Models\TandaTangan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratIzinPenelitianControllerTest extends TestCase
{
    protected $user;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create level table
        Schema::create('level', function ($table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        // 2. Create users table
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('level_id');
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 3. Create prodi table
        Schema::create('prodi', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('kode')->nullable();
            $table->string('alias')->nullable();
            $table->string('nama_kepala')->nullable();
            $table->string('nidn_kepala')->nullable();
            $table->timestamps();
        });

        // 4. Create tanda_tangan table
        Schema::create('tanda_tangan', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();
        });

        // 5. Create fakultas table
        Schema::create('fakultas', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('dekan')->nullable();
            $table->string('nidn_dekan')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->string('alias')->nullable();
            $table->timestamps();
        });

        // 6. Create fakultas_prodi table
        Schema::create('fakultas_prodi', function ($table) {
            $table->id();
            $table->unsignedBigInteger('fakultas_id');
            $table->unsignedBigInteger('prodi_id');
            $table->timestamps();
        });

        // 7. Create nomor table
        Schema::create('nomor', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        // 8. Create log_surat table
        Schema::create('log_surat', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_surat')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        // 9. Create jenis_surat table
        Schema::create('jenis_surat', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('alias')->nullable();
            $table->string('format_surat')->nullable();
            $table->timestamps();
        });

        // 10. Create surat_izin_penelitian table
        Schema::create('surat_izin_penelitian', function ($table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama');
            $table->string('nim');
            $table->string('prodi_mhs')->nullable();
            $table->string('kepada')->nullable();
            $table->string('semester');
            $table->date('dari_tanggal');
            $table->date('tanggal');
            $table->integer('prodi_id');
            $table->integer('user_id');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('drive_file_id')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->timestamps();
        });

        // 11. Populate basic dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->prodi = Prodi::create([
            'id' => 10,
            'nama' => 'Teknik Informatika',
            'alias' => 'TI',
            'nama_kepala' => 'Dr. H. Ahmad',
            'nidn_kepala' => '12345678'
        ]);

        $this->user = User::factory()->create([
            'level_id' => 1,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama' => 'Surat Izin Penelitian',
            'alias' => 'SIP',
            'format_surat' => 'SU-{NO}/UII.085/{PRODI}/TL.00/{BULAN}/{TAHUN}'
        ]);
    }

    public function test_index_returns_paginated_surat_izin_penelitian()
    {
        SuratIzinPenelitian::create([
            'nomor' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama' => 'Mhs Laki-Laki',
            'nim' => '111222333',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-izin-penelitian');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('111222333', $data[0]['nim']);
    }

    public function test_index_can_search_surat_izin_penelitian()
    {
        SuratIzinPenelitian::create([
            'nomor' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama' => 'Ahmad Laki',
            'nim' => '111222333',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        SuratIzinPenelitian::create([
            'nomor' => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama' => 'Budi Laki',
            'nim' => '444555666',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-izin-penelitian?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi Laki', $data[0]['nama']);
    }

    public function test_store_creates_surat_izin_penelitian_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '123',
            'nama' => 'Tester Mahasiswa',
            'nim' => '987654321',
            'prodi_mhs' => 'Teknik Informatika',
            'kepada' => 'Kepala Lab',
            'semester' => 'Ganjil',
            'dari_tanggal' => '2026-06-01',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-izin-penelitian', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in surat_izin_penelitian
        $formatted = 'SU-123/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_izin_penelitian', [
            'nim' => '987654321',
            'nomor' => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => '123',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '123',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Izin Penelitian',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama' => 'Missing fields',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-izin-penelitian', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['prodi_id', 'no_surat', 'nim', 'semester', 'dari_tanggal', 'tanggal']);
    }

    public function test_show_returns_surat_izin_penelitian()
    {
        $sip = SuratIzinPenelitian::create([
            'nomor' => 'SU-005/UII.085/TI/TL.00/05/2026',
            'nama' => 'Show Tester',
            'nim' => '555555',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-izin-penelitian/' . $sip->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show Tester', $response->json('data.nama'));
        $this->assertEquals('005', $response->json('data.no_surat')); // Extracted from SU-005/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-izin-penelitian/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_surat_izin_penelitian_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $sip = SuratIzinPenelitian::create([
            'nomor' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama' => 'Before Update',
            'nim' => '777777',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100-UPD', // New number
            'nama' => 'After Update',
            'nim' => '777777',
            'prodi_mhs' => 'Teknik Informatika',
            'kepada' => 'Pihak Lab Baru',
            'semester' => 'Ganjil',
            'dari_tanggal' => '2026-06-01',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-izin-penelitian/' . $sip->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check if database updated
        $formatted = 'SU-100-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_izin_penelitian', [
            'id' => $sip->id,
            'nama' => 'After Update',
            'nomor' => $formatted,
            'kepada' => 'Pihak Lab Baru'
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '100-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '100-UPD',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Izin Penelitian',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $sip = SuratIzinPenelitian::create([
            'nomor' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama' => 'Before Update',
            'nim' => '777777',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'nama' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-izin-penelitian/' . $sip->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama', 'no_surat', 'prodi_id', 'nim', 'semester', 'dari_tanggal', 'tanggal']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100',
            'nama' => 'No One',
            'nim' => '000000',
            'semester' => 'Ganjil',
            'dari_tanggal' => '2026-06-01',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-izin-penelitian/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_surat_izin_penelitian()
    {
        $sip = SuratIzinPenelitian::create([
            'nomor' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama' => 'Before Delete',
            'nim' => '777777',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'dari_tanggal' => '2026-05-26',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-izin-penelitian/' . $sip->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_izin_penelitian', ['id' => $sip->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-izin-penelitian/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
