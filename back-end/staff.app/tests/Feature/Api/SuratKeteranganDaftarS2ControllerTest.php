<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganDaftarS2;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganDaftarS2ControllerTest extends TestCase
{
    protected $user;
    protected $prodi;
    protected $tandaTanganStaff;
    protected $settingStaff;

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
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        // 4. Create tanda_tangan table
        Schema::create('tanda_tangan', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();
        });

        // 5. Create setting_jabatan table
        Schema::create('setting_jabatan', function ($table) {
            $table->id();
            $table->string('kunci_jabatan')->unique();
            $table->string('nama_jabatan');
            $table->string('nidn')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        // 6. Create fakultas table
        Schema::create('fakultas', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('dekan')->nullable();
            $table->string('nidn')->nullable();
            $table->string('nidn_dekan')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->string('alias')->nullable();
            $table->timestamps();
        });

        // 7. Create fakultas_prodi table
        Schema::create('fakultas_prodi', function ($table) {
            $table->id();
            $table->unsignedBigInteger('fakultas_id');
            $table->unsignedBigInteger('prodi_id');
            $table->timestamps();
        });

        // 8. Create nomor table
        Schema::create('nomor', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        // 9. Create log_surat table
        Schema::create('log_surat', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_surat')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        // 10. Create jenis_surat table
        Schema::create('jenis_surat', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('alias')->nullable();
            $table->string('format_surat')->nullable();
            $table->timestamps();
        });

        // 11. Create surat_keterangan_daftar_s2 table
        Schema::create('surat_keterangan_daftar_s2', function ($table) {
            $table->id();
            $table->string('nomor_surat');
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nim')->nullable();
            $table->string('prodi');
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->date('tanggal');
            $table->integer('user_id');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });

        // Populate basic dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->tandaTanganStaff = TandaTangan::create([
            'nama' => 'Staff Prodi S2 TTD',
            'gambar' => 'img/staff_s2_ttd.png'
        ]);

        $this->prodi = Prodi::create([
            'id' => 10,
            'nama' => 'Teknik Informatika',
            'alias' => 'TI',
            'nama_kepala' => 'Dr. H. Ahmad',
            'nidn_kepala' => '12345678',
            'tanda_tangan_id' => $this->tandaTanganStaff->id
        ]);

        $this->settingStaff = SettingJabatan::create([
            'kunci_jabatan' => 'staff_ti',
            'nama_jabatan' => 'Staff Prodi TI',
            'tanda_tangan_id' => $this->tandaTanganStaff->id
        ]);

        $this->user = User::factory()->create([
            'level_id' => 1,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama' => 'Surat Keterangan Daftar S2',
            'alias' => 'SKMS',
            'format_surat' => 'SU-{NO}/UII.085/TI/TL.00/{BULAN}/{TAHUN}'
        ]);
    }

    public function test_index_returns_paginated_daftar_s2()
    {
        SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Mhs S2 Laki',
            'nim' => '111444777',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skds2');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('111444777', $data[0]['nim']);
    }

    public function test_index_can_search_daftar_s2()
    {
        SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Ahmad S2',
            'nim' => '111444777',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Budi S2',
            'nim' => '222555888',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skds2?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi S2', $data[0]['nama_lengkap']);
    }

    public function test_store_creates_daftar_s2_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '678',
            'nama_lengkap' => 'Galih S2',
            'nim' => '555444333',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skds2', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in database
        $formatted = 'SU-678/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_daftar_s2', [
            'nim' => '555444333',
            'nomor_surat' => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => '678',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '678',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Daftar S2',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_lengkap' => 'Galih Missing',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skds2', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['prodi_id', 'no_surat', 'nim', 'prodi', 'tanggal']);
    }

    public function test_show_returns_daftar_s2()
    {
        $data = SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-123/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Show S2',
            'nim' => '111222333',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skds2/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show S2', $response->json('data.nama_lengkap'));
        $this->assertEquals('123', $response->json('data.no_surat')); // Extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skds2/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_daftar_s2_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '999-UPD', // New number
            'nama_lengkap' => 'After Update',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skds2/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check database update
        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_daftar_s2', [
            'id' => $data->id,
            'nama_lengkap' => 'After Update',
            'nomor_surat' => $formatted
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '999-UPD',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Daftar S2',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'nama_lengkap' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skds2/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama_lengkap', 'no_surat', 'prodi_id', 'nim', 'prodi', 'tanggal']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100',
            'nama_lengkap' => 'No One',
            'nim' => '000000',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skds2/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_daftar_s2()
    {
        $data = SuratKeteranganDaftarS2::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Delete',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skds2/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_daftar_s2', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skds2/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
