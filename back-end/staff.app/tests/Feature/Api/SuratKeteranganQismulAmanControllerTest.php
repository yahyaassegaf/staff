<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganQismulAman;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganQismulAmanControllerTest extends TestCase
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

        // 11. Create surat_keterangan_qismul_aman table
        Schema::create('surat_keterangan_qismul_aman', function ($table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('ketua')->nullable();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nim');
            $table->integer('prodi_id')->nullable();
            $table->integer('user_id');
            $table->string('prodi_mhs');
            $table->text('alamat_rumah');
            $table->string('kelas_pondok');
            $table->date('tanggal_berlaku_dari');
            $table->date('tanggal_berlaku_sampai');
            $table->date('tanggal');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });

        // Populate basic dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->tandaTanganStaff = TandaTangan::create([
            'nama' => 'Ketua Qismul Aman TTD',
            'gambar' => 'img/qismul_aman_ttd.png'
        ]);

        $this->settingJabatan = SettingJabatan::create([
            'kunci_jabatan' => 'ketua_qismul_aman',
            'nama_jabatan' => 'Ketua Qismul Aman',
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
            'nama' => 'Surat Keterangan Qismul Aman',
            'alias' => 'SKQA',
            'format_surat' => 'SU-{NO}/UII.085/TI/TL.00/{BULAN}/{TAHUN}'
        ]);

        $this->fakultas = DB::table('fakultas')->insertGetId([
            'nama' => 'Fakultas Teknik',
            'dekan' => 'Dekan Teknik',
            'nidn' => '88888888',
            'nidn_dekan' => '88888888',
            'tanda_tangan_id' => $this->tandaTanganStaff->id,
            'alias' => 'FT',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('fakultas_prodi')->insert([
            'fakultas_id' => $this->fakultas,
            'prodi_id' => $this->prodi->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function test_index_returns_paginated_daftar_qismul_aman()
    {
        SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Mhs QA Laki',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '333444555',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skqa');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim']);
    }

    public function test_index_can_search_daftar_qismul_aman()
    {
        SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Ahmad QA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '333444555',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Budi QA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '666777888',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skqa?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi QA', $data[0]['nama_lengkap']);
    }

    public function test_store_creates_daftar_qismul_aman_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '789',
            'nama_mhs' => 'Galih QA',
            'tempat_lahir' => 'Bangil',
            'tanggal_lahir' => '2001-02-03',
            'nim' => '999888777',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Kenanga No. 5',
            'kelas_pondok' => 'C/4',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skqa', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        // Verify record in database
        $formatted = 'SU-789/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_qismul_aman', [
            'nim' => '999888777',
            'nomor_surat' => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'ketua' => 'Ketua Qismul Aman TTD' // Seeded setting_jabatan -> tanda_tangan -> nama
        ]);

        // Verify audit logs
        $this->assertDatabaseHas('nomor', [
            'nomor' => '789',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '789',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Qismul Aman',
            'user_id' => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_mhs' => 'Missing Fields',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/skqa', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['no_surat', 'tempat_lahir', 'tanggal_lahir', 'nim', 'prodi_mhs', 'alamat_rumah', 'kelas_pondok', 'tanggal_berlaku_dari', 'tanggal_berlaku_sampai', 'tanggal']);
    }

    public function test_show_returns_daftar_qismul_aman()
    {
        $data = SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-123/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Show QA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '111222333',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skqa/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('Show QA', $response->json('data.nama_lengkap'));
        $this->assertEquals('123', $response->json('data.no_surat')); // Extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/skqa/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_update_modifies_daftar_qismul_aman_and_logs_audit_trail_when_new_no_surat()
    {
        // Fake Google Drive storage and queues to isolate PDF generation and jobs
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '999999',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '999-UPD', // New number
            'nama_mhs' => 'After Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '999999',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA Baru',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skqa/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        // Check database update
        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_qismul_aman', [
            'id' => $data->id,
            'nama_lengkap' => 'After Update',
            'nomor_surat' => $formatted,
            'alamat_rumah' => 'Jl. Test QA Baru',
            'ketua' => 'Ketua Qismul Aman TTD' // Seeded setting_jabatan -> tanda_tangan -> nama
        ]);

        // Check if new number logged
        $this->assertDatabaseHas('nomor', [
            'nomor' => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor' => '999-UPD',
            'nomor_surat' => $formatted,
            'nama_surat' => 'Surat Keterangan Qismul Aman',
            'user_id' => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '999999',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $payload = [
            'nama_mhs' => '', // Required
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skqa/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['nama_mhs', 'no_surat', 'tempat_lahir', 'tanggal_lahir', 'nim', 'prodi_mhs', 'alamat_rumah', 'kelas_pondok', 'tanggal_berlaku_dari', 'tanggal_berlaku_sampai', 'tanggal']);
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
            'kelas_pondok' => 'A/1',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/skqa/999', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    public function test_destroy_deletes_daftar_qismul_aman()
    {
        $data = SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Delete',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '999999',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skqa/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_qismul_aman', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/skqa/999');

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

        $data = SuratKeteranganQismulAman::forceCreate([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Download QA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '333444555',
            'prodi_id' => $this->prodi->id,
            'user_id' => $this->user->id,
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test QA',
            'kelas_pondok' => 'B/3',
            'tanggal_berlaku_dari' => '2026-05-01',
            'tanggal_berlaku_sampai' => '2026-08-01',
            'tanggal' => '2026-05-26',
            'jenis_kelamin' => 'L',
            'status' => 'pending'
        ]);

        $response = $this->get('/api/skqa/download-pdf/' . $data->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('inline; filename="surat_keterangan_qismul_aman_333444555.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function test_download_pdf_returns_404_if_not_found()
    {
        $response = $this->get('/api/skqa/download-pdf/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
