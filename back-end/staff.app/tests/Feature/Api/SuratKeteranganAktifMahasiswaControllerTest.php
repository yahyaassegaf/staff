<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganAktifMahasiswa;
use App\Models\TandaTangan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganAktifMahasiswaControllerTest extends TestCase
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
            $table->string('nidn')->nullable();
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

        // 10. Create surat_keterangan_aktif_mahasiswa table
        Schema::create('surat_keterangan_aktif_mahasiswa', function ($table) {
            $table->id();
            $table->string('nomor_surat');
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->unsignedBigInteger('th_akademik_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nim')->nullable();
            $table->string('nik')->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('prodi_mhs');
            $table->string('semester');
            $table->string('tahun_akademik');
            $table->string('nama_ortu');
            $table->string('nik_ortu')->nullable();
            $table->string('nip_ortu')->nullable();
            $table->text('alamat_ortu');
            $table->string('hp_ortu')->nullable();
            $table->date('tanggal');
            $table->integer('user_id');
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

        $this->user = User::factory()->create([
            'level_id' => 1,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama' => 'Surat Keterangan Aktif Mahasiswa',
            'alias' => 'SKAM',
            'format_surat' => 'SU-{NO}/UII.085/TI/TL.00/{BULAN}/{TAHUN}'
        ]);
    }

    public function test_index_returns_paginated_aktif_mahasiswa()
    {
        SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Mhs Aktif Laki',
            'nim' => '333444555',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skam');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim']);
    }

    public function test_index_can_search_aktif_mahasiswa()
    {
        SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Ahmad Aktif',
            'nim' => '333444555',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Budi Aktif',
            'nim' => '666777888',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skam?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi Aktif', $data[0]['nama_lengkap']);
    }

    public function test_store_creates_aktif_mahasiswa_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '567',
            'nama_mhs' => 'Faisal Aktif',
            'nim' => '777666555',
            'nik' => '1234567890',
            'tempat_lahir' => 'Bangil',
            'tanggal_lahir' => '2002-04-04',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'V',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Faisal',
            'nik_ortu' => '0987654321',
            'alamat_ortu' => 'Jl. Mawar No. 10',
            'hp_ortu' => '0812345678',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skam', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in database
        $formatted = 'SU-567/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_aktif_mahasiswa', [
            'nim' => '777666555',
            'nomor_surat' => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => '567',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '567',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Aktif Mahasiswa',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_mhs' => 'Faisal Missing',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skam', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['no_surat', 'nim', 'tempat_lahir', 'tanggal_lahir', 'prodi_mhs', 'semester', 'tahun_akademik', 'nama_ortu', 'alamat_ortu', 'tanggal']);
    }

    public function test_show_returns_aktif_mahasiswa()
    {
        $data = SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-111/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Show Aktif',
            'nim' => '111222333',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skam/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show Aktif', $response->json('data.nama_lengkap'));
        $this->assertEquals('111', $response->json('data.no_surat')); // Extracted from SU-111/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skam/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_aktif_mahasiswa_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '999-UPD', // New number
            'nama_mhs' => 'After Update',
            'nim' => '999999',
            'nik' => '1234567890',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif Baru',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skam/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check database update
        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_aktif_mahasiswa', [
            'id' => $data->id,
            'nama_lengkap' => 'After Update',
            'nomor_surat' => $formatted,
            'alamat_ortu' => 'Jl. Test Aktif Baru'
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '999-UPD',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Aktif Mahasiswa',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'nama_mhs' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skam/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama_mhs', 'no_surat', 'prodi_id', 'nim', 'tempat_lahir', 'tanggal_lahir', 'prodi_mhs', 'semester', 'tahun_akademik', 'nama_ortu', 'alamat_ortu', 'tanggal']);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100',
            'nama_mhs' => 'No One',
            'nim' => '000000',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-05-05',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak No One',
            'alamat_ortu' => 'Jl. No Where',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skam/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_aktif_mahasiswa()
    {
        $data = SuratKeteranganAktifMahasiswa::create([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Delete',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'semester' => 'Genap',
            'tahun_akademik' => '2025/2026',
            'nama_ortu' => 'Bapak Aktif',
            'alamat_ortu' => 'Jl. Test Aktif',
            'tanggal' => '2026-05-26',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skam/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_aktif_mahasiswa', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skam/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
