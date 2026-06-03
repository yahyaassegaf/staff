<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganSpm;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganSpmControllerTest extends TestCase
{
    protected $user;
    protected $prodi;
    protected $tandaTanganStaff;
    protected $settingJabatan;
    protected $fakultas;

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

        // 6. Create nomor table
        Schema::create('nomor', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        // 7. Create log_surat table
        Schema::create('log_surat', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_surat')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        // 8. Create jenis_surat table
        Schema::create('jenis_surat', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('alias')->nullable();
            $table->string('format_surat')->nullable();
            $table->timestamps();
        });

        // 9. Create surat_keterangan_spm table
        Schema::create('surat_keterangan_spm', function ($table) {
            $table->id();
            $table->string('nomor_surat');
            $table->integer('prodi_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nim')->nullable();
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('prodi_mhs');
            $table->text('alamat');
            $table->string('nama_ortu');
            $table->string('tempat_tugas');
            $table->text('alamat_tugas');
            $table->string('tahun');
            $table->string('semester');
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
            'nama' => 'Staff TTD',
            'gambar' => 'img/staff_ttd.png'
        ]);

        $this->settingJabatan = SettingJabatan::create([
            'kunci_jabatan' => 'pengawas_spm',
            'nama_jabatan' => 'Pengawas SPM',
            'tanda_tangan_id' => $this->tandaTanganStaff->id
        ]);

        $this->prodi = Prodi::create([
            'id' => 10,
            'nama' => 'Teknik Informatika',
            'alias' => 'TI',
            'nama_kepala' => 'Dr. H. Ahmad',
            'nidn_kepala' => '12345678',
            'tanda_tangan_id' => $this->tandaTanganStaff->id
        ]);

        $this->user = User::factory()->create([
            'level_id' => 1,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama' => 'Surat Keterangan SPM',
            'alias' => 'STSPM',
            'format_surat' => 'SU-{NO}/UII.085/TI/TL.00/{BULAN}/{TAHUN}'
        ]);
    }

    public function test_index_returns_paginated_daftar_spm()
    {
        SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Mhs SPM Laki',
            'nim' => '333444555',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas',
            'alamat_tugas' => 'Alamat Tugas',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spm');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim']);
    }

    public function test_index_can_search_daftar_spm()
    {
        SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Ahmad SPM',
            'nim' => '333444555',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas A',
            'alamat_tugas' => 'Alamat Tugas A',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-002/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Budi SPM',
            'nim' => '666777888',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas B',
            'alamat_tugas' => 'Alamat Tugas B',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spm?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi SPM', $data[0]['nama_lengkap']);
    }

    public function test_store_creates_daftar_spm_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '789',
            'nama_lengkap' => 'Galih SPM',
            'nim' => '999888777',
            'tempat_lahir' => 'Bangil',
            'tanggal_lahir' => '2001-02-03',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Kenanga No. 5',
            'nama_ortu' => 'Ortu Galih',
            'tempat_tugas' => 'Tempat Tugas Galih',
            'alamat_tugas' => 'Alamat Tugas Galih',
            'tahun' => '2026',
            'semester' => 'Ganjil',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/spm', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in database
        $formatted = 'SU-789/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_spm', [
            'nim' => '999888777',
            'nomor_surat' => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => '789',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '789',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan SPM',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_lengkap' => 'Missing Fields',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/spm', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors([
                     'prodi_id', 'no_surat', 'nim', 'tempat_lahir', 'tanggal_lahir', 
                     'prodi_mhs', 'alamat', 'nama_ortu', 'tempat_tugas', 'alamat_tugas', 
                     'tahun', 'semester', 'tanggal'
                 ]);
    }

    public function test_show_returns_daftar_spm()
    {
        $data = SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-123/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Show SPM',
            'nim' => '111222333',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas',
            'alamat_tugas' => 'Alamat Tugas',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spm/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show SPM', $response->json('data.nama_lengkap'));
        $this->assertEquals('123', $response->json('data.no_surat')); // Extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spm/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_daftar_spm_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Before Update',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas',
            'alamat_tugas' => 'Alamat Tugas',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '999-UPD', // New number
            'nama_lengkap' => 'After Update',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM Baru',
            'nama_ortu' => 'Ortu SPM Baru',
            'tempat_tugas' => 'Tempat Tugas Baru',
            'alamat_tugas' => 'Alamat Tugas Baru',
            'tahun' => '2026',
            'semester' => 'Ganjil',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/spm/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check database update
        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_spm', [
            'id' => $data->id,
            'nama_lengkap' => 'After Update',
            'nomor_surat' => $formatted,
            'alamat' => 'Jl. Test SPM Baru',
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '999-UPD',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan SPM',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Before Update',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas',
            'alamat_tugas' => 'Alamat Tugas',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'nama_lengkap' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/spm/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'prodi_id', 'no_surat', 'nama_lengkap', 'nim', 'tempat_lahir', 'tanggal_lahir', 
                     'prodi_mhs', 'alamat', 'nama_ortu', 'tempat_tugas', 'alamat_tugas', 
                     'tahun', 'semester', 'tanggal'
                 ]);
    }

    public function test_update_returns_404_if_not_found()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100',
            'nama_lengkap' => 'No One',
            'nim' => '000000',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2000-05-05',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. No Where',
            'nama_ortu' => 'No Ortu',
            'tempat_tugas' => 'No Tempat',
            'alamat_tugas' => 'No Alamat',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/spm/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_daftar_spm()
    {
        $data = SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Before Delete',
            'nim' => '999999',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas',
            'alamat_tugas' => 'Alamat Tugas',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/spm/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_spm', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/spm/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_download_pdf_returns_pdf()
    {
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganSpm::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Download SPM',
            'nim' => '333444555',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat' => 'Jl. Test SPM',
            'nama_ortu' => 'Ortu SPM',
            'tempat_tugas' => 'Tempat Tugas',
            'alamat_tugas' => 'Alamat Tugas',
            'tahun' => '2026',
            'semester' => 'Genap',
            'tanggal' => '2026-05-26',
            'user_id' => $this->user->id,
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->get('/api/spm/download-pdf/' . $data->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline; filename="surat_keterangan_spm_333444555.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function test_download_pdf_returns_404_if_not_found()
    {
        $response = $this->get('/api/spm/download-pdf/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
