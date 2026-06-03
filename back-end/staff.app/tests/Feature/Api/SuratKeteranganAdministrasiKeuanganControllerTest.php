<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganAdministrasiKeuangan;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganAdministrasiKeuanganControllerTest extends TestCase
{
    protected $user;
    protected $prodi;
    protected $settingKeuangan;
    protected $tandaTanganKeuangan;

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

        // 11. Create surat_keterangan_administrasi_keuangan table
        Schema::create('surat_keterangan_administrasi_keuangan', function ($table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('kepala_biro')->nullable();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nim');
            $table->string('prodi_mhs');
            $table->integer('user_id');
            $table->integer('prodi_id')->nullable();
            $table->text('alamat_rumah');
            $table->string('kelas_pondok');
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->date('tanggal');
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
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

        $this->tandaTanganKeuangan = TandaTangan::create([
            'nama' => 'Prof. Dr. Keuangan TTD',
            'gambar' => 'img/keuangan_ttd.png'
        ]);

        $this->settingKeuangan = SettingJabatan::create([
            'kunci_jabatan' => 'kepala_biro_keuangan',
            'nama_jabatan' => 'Kepala Biro Administrasi Keuangan',
            'tanda_tangan_id' => $this->tandaTanganKeuangan->id
        ]);

        $this->user = User::factory()->create([
            'level_id' => 1,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama' => 'Surat Keterangan Administrasi Keuangan',
            'alias' => 'SKAK',
            'format_surat' => 'SU-{NO}/UII.085/BAK/KU.01.2/{BULAN}/{TAHUN}'
        ]);
    }

    public function test_index_returns_paginated_administrasi_keuangan()
    {
        SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-001/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Mhs Keuangan Laki',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '222333444',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Test Keuangan',
            'kelas_pondok' => 'Ibnu Sina',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skak');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('222333444', $data[0]['nim']);
    }

    public function test_index_can_search_administrasi_keuangan()
    {
        SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-001/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Ahmad Keuangan',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '222333444',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Test Keuangan',
            'kelas_pondok' => 'Ibnu Sina',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-002/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Budi Keuangan',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '555666777',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Test Keuangan 2',
            'kelas_pondok' => 'Al-Ghazali',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skak?search=Ghazali');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi Keuangan', $data[0]['nama_lengkap']);
    }

    public function test_store_creates_administrasi_keuangan_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '456',
            'nama_mhs' => 'Rahmat Keuangan',
            'tempat_lahir' => 'Malang',
            'tanggal_lahir' => '2001-02-03',
            'nim' => '999888777',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Keuangan Indah No. 5',
            'kelas_pondok' => 'Kelas C',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skak', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in database
        $formatted = 'SU-456/UII.085/BAK/KU.01.2/05/2026';
        $this->assertDatabaseHas('surat_keterangan_administrasi_keuangan', [
            'nim' => '999888777',
            'nomor_surat' => $formatted,
            'prodi_id' => $this->prodi->id,
            'kepala_biro' => 'Prof. Dr. Keuangan TTD', // Resolved from setting_jabatan
            'user_id' => $this->user->id
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => '456',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '456',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Administrasi Keuangan',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_mhs' => 'Rahmat Missing',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skak', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['no_surat', 'tempat_lahir', 'tanggal_lahir', 'nim', 'prodi_mhs', 'alamat_rumah', 'kelas_pondok', 'tanggal']);
    }

    public function test_show_returns_administrasi_keuangan()
    {
        $data = SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-789/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Show Keuangan',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '11112222',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Show Keuangan',
            'kelas_pondok' => 'Ibnu Sina',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skak/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show Keuangan', $response->json('data.nama_lengkap'));
        $this->assertEquals('789', $response->json('data.no_surat')); // Extracted from SU-789/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skak/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_administrasi_keuangan_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-001/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Before Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '888888',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Before Update',
            'kelas_pondok' => 'Ibnu Sina',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '888-UPD', // New number
            'nama_mhs' => 'After Update',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-05-05',
            'nim' => '888888',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. After Update',
            'kelas_pondok' => 'Al-Farabi',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skak/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check database update
        $formatted = 'SU-888-UPD/UII.085/BAK/KU.01.2/05/2026';
        $this->assertDatabaseHas('surat_keterangan_administrasi_keuangan', [
            'id' => $data->id,
            'nama_lengkap' => 'After Update',
            'nomor_surat' => $formatted,
            'alamat_rumah' => 'Jl. After Update'
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '888-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '888-UPD',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Administrasi Keuangan',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-001/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Before Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '888888',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Before Update',
            'kelas_pondok' => 'Ibnu Sina',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        $payload = [
            'nama_mhs' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skak/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama_mhs', 'no_surat', 'prodi_id', 'tempat_lahir', 'tanggal_lahir', 'nim', 'prodi_mhs', 'alamat_rumah', 'kelas_pondok', 'tanggal']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100',
            'nama_mhs' => 'No One',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-05-05',
            'nim' => '000000',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. No Where',
            'kelas_pondok' => 'Al-Farabi',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skak/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_administrasi_keuangan()
    {
        $data = SuratKeteranganAdministrasiKeuangan::create([
            'nomor_surat' => 'SU-001/UII.085/BAK/KU.01.2/05/2026',
            'nama_lengkap' => 'Before Delete',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '888888',
            'prodi_mhs' => 'Teknik Informatika',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'alamat_rumah' => 'Jl. Before Delete',
            'kelas_pondok' => 'Ibnu Sina',
            'jenis_kelamin' => 'L',
            'tanggal' => '2026-05-26',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skak/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_administrasi_keuangan', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skak/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
