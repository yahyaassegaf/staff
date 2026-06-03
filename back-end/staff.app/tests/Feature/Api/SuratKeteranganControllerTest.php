<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeterangan;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganControllerTest extends TestCase
{
    protected $user;
    protected $prodi;
    protected $tandaTangan;
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

        // 11. Create surat_keterangan table
        Schema::create('surat_keterangan', function ($table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama_mahasiswa');
            $table->string('prodi_mhs')->nullable();
            $table->string('nim');
            $table->string('prodi');
            $table->string('periode_bulan');
            $table->string('nama_staff')->nullable();
            $table->text('alasan');
            $table->date('tanggal');
            $table->integer('user_id');
            $table->integer('prodi_id');
            $table->integer('tanda_tangan_id')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });

        // Populate basic dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->prodi = Prodi::create([
            'id' => 10,
            'nama' => 'Teknik Informatika',
            'alias' => 'TI',
            'nama_kepala' => 'Dr. H. Ahmad',
            'nidn_kepala' => '12345678'
        ]);

        $this->tandaTangan = TandaTangan::create([
            'nama' => 'Staff Prodi TTD',
            'gambar' => 'img/staff_ttd.png'
        ]);

        $this->settingStaff = SettingJabatan::create([
            'kunci_jabatan' => 'staff_ti',
            'nama_jabatan' => 'Staff Prodi TI',
            'tanda_tangan_id' => $this->tandaTangan->id
        ]);

        $this->user = User::factory()->create([
            'level_id' => 1,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);
    }

    public function test_index_returns_paginated_surat_keterangan()
    {
        SuratKeterangan::create([
            'nomor' => 'SU-001/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Mhs Keterangan Laki',
            'nim' => '777111222',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Untuk Keperluan Test',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('777111222', $data[0]['nim']);
    }

    public function test_index_can_search_surat_keterangan()
    {
        SuratKeterangan::create([
            'nomor' => 'SU-001/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Ahmad Keterangan',
            'nim' => '777111222',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Untuk Keperluan Test',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        SuratKeterangan::create([
            'nomor' => 'SU-002/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Budi Keterangan',
            'nim' => '555222333',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Untuk Keperluan Test',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi Keterangan', $data[0]['nama_mahasiswa']);
    }

    public function test_store_creates_surat_keterangan_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'nama_mhs' => 'Candra Keterangan',
            'nim' => '999111333',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Untuk Mengurus Beasiswa',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-keterangan', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in database (gets padded to 3 digits)
        $no_surat = '001';
        $formatted = 'SU-001/UII.085/K.TI/PP.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan', [
            'nim' => '999111333',
            'nomor' => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => $no_surat,
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => $no_surat,
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_mhs' => 'Candra Missing',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-keterangan', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['prodi_id', 'tanda_tangan_id', 'nim', 'prodi', 'periode_bulan', 'alasan', 'tanggal']);
    }

    public function test_show_returns_surat_keterangan()
    {
        $data = SuratKeterangan::create([
            'nomor' => 'SU-005/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Show Keterangan',
            'nim' => '111222333',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Untuk Keperluan Test',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show Keterangan', $response->json('data.nama_mahasiswa'));
        $this->assertEquals('005', $response->json('data.no_surat')); // Extracted from SU-005/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_surat_keterangan_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeterangan::create([
            'nomor' => 'SU-001/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Before Update',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Sebelum Update',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'no_surat' => '999', // New number
            'nama_mhs' => 'After Update',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Setelah Update',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-keterangan/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check database update
        $formatted = 'SU-999/UII.085/K.TI/PP.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan', [
            'id' => $data->id,
            'nama_mahasiswa' => 'After Update',
            'nomor' => $formatted,
            'alasan' => 'Setelah Update'
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '999',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '999',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeterangan::create([
            'nomor' => 'SU-001/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Before Update',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Sebelum Update',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'nama_mhs' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-keterangan/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama_mhs', 'no_surat', 'prodi_id', 'tanda_tangan_id', 'nim', 'prodi', 'periode_bulan', 'alasan', 'tanggal']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'no_surat' => '100',
            'nama_mhs' => 'No One',
            'nim' => '000000',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Setelah Update',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-keterangan/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_surat_keterangan()
    {
        $data = SuratKeterangan::create([
            'nomor' => 'SU-001/UII.085/K.TI/PP.00/05/2026',
            'nama_mahasiswa' => 'Before Delete',
            'nim' => '999999',
            'prodi' => 'Teknik Informatika',
            'periode_bulan' => 'Mei 2026',
            'alasan' => 'Sebelum Delete',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-keterangan/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-keterangan/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
