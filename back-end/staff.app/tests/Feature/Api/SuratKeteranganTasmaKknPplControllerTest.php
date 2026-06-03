<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganTasmaKknPpl;
use App\Models\TandaTangan;
use App\Models\SettingJabatan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganTasmaKknPplControllerTest extends TestCase
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

        // 11. Create surat_keterangan_tasma_kkn_ppl table
        Schema::create('surat_keterangan_tasma_kkn_ppl', function ($table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('ketua')->nullable();
            $table->integer('tanda_tangan_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nim');
            $table->integer('prodi_id')->nullable();
            $table->integer('user_id');
            $table->string('prodi_mhs');
            $table->text('alamat_rumah');
            $table->string('kelas_pondok');
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
            'nama' => 'Ketua TASMA TTD',
            'gambar' => 'img/ketua_tasma_ttd.png'
        ]);

        $this->settingJabatan = SettingJabatan::create([
            'kunci_jabatan' => 'ketua_tasma',
            'nama_jabatan'  => 'Ketua TASMA',
            'tanda_tangan_id' => $this->tandaTanganStaff->id
        ]);

        $this->prodi = Prodi::create([
            'id'          => 10,
            'nama'        => 'Teknik Informatika',
            'alias'       => 'TI',
            'nama_kepala' => 'Dr. H. Ahmad',
            'nidn_kepala' => '12345678',
            'tanda_tangan_id' => $this->tandaTanganStaff->id
        ]);

        $this->user = User::factory()->create([
            'level_id'      => 1,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama'         => 'Surat Keterangan TASMA, KKN, PPL',
            'alias'        => 'STTKP',
            'format_surat' => 'SU-{NO}/UII.085/TI/TL.00/{BULAN}/{TAHUN}'
        ]);

        $this->fakultas = DB::table('fakultas')->insertGetId([
            'nama'         => 'Fakultas Teknik',
            'dekan'        => 'Dekan Teknik',
            'nidn'         => '88888888',
            'nidn_dekan'   => '88888888',
            'tanda_tangan_id' => $this->tandaTanganStaff->id,
            'alias'        => 'FT',
            'created_at'   => now(),
            'updated_at'   => now()
        ]);

        DB::table('fakultas_prodi')->insert([
            'fakultas_id' => $this->fakultas,
            'prodi_id'    => $this->prodi->id,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_daftar_tasma_kkn_ppl()
    {
        SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-001/UII.085/TI/TL.00/05/2026',
            'ketua'        => 'Ketua TASMA TTD',
            'nama_lengkap' => 'Mhs TASMA Laki',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '333444555',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test TASMA',
            'kelas_pondok' => 'B/3',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/sktkp');

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim']);
    }

    public function test_index_can_search_daftar_tasma_kkn_ppl()
    {
        SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Ahmad TASMA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '111222333',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test',
            'kelas_pondok' => 'A/1',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Budi TASMA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '444555666',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test 2',
            'kelas_pondok' => 'B/2',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/sktkp?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi TASMA', $data[0]['nama_lengkap']);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_creates_daftar_tasma_kkn_ppl_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id'      => $this->prodi->id,
            'no_surat'      => '789',
            'nama_mhs'      => 'Galih TASMA',
            'tempat_lahir'  => 'Bangil',
            'tanggal_lahir' => '2001-02-03',
            'nim'           => '999888777',
            'prodi_mhs'     => 'Teknik Informatika',
            'alamat_rumah'  => 'Jl. Kenanga No. 5',
            'kelas_pondok'  => 'C/4',
            'tanggal'       => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/sktkp', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $formatted = 'SU-789/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_tasma_kkn_ppl', [
            'nim'         => '999888777',
            'nomor_surat' => $formatted,
            'prodi_id'    => $this->prodi->id,
            'user_id'     => $this->user->id,
            'ketua'       => 'Ketua TASMA TTD', // seeded from setting_jabatan -> tanda_tangan
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '789',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'       => '789',
            'nomor_surat' => $formatted,
            'nama_surat'  => 'Surat Keterangan TASMA, KKN, PPL',
            'user_id'     => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = [
            'nama_mhs' => 'Missing Fields',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/sktkp', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors([
                     'no_surat', 'tempat_lahir', 'tanggal_lahir', 'nim',
                     'prodi_mhs', 'alamat_rumah', 'kelas_pondok', 'tanggal'
                 ]);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_returns_daftar_tasma_kkn_ppl()
    {
        $data = SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-123/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Show TASMA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '111222333',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test TASMA',
            'kelas_pondok' => 'B/3',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/sktkp/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $this->assertEquals('Show TASMA', $response->json('data.nama_lengkap'));
        $this->assertEquals('123', $response->json('data.no_surat')); // Extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/sktkp/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_modifies_daftar_tasma_kkn_ppl_and_logs_audit_trail_when_new_no_surat()
    {
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '999999',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test TASMA',
            'kelas_pondok' => 'B/3',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $payload = [
            'prodi_id'      => $this->prodi->id,
            'no_surat'      => '999-UPD', // New number
            'nama_mhs'      => 'After Update',
            'tempat_lahir'  => 'Pasuruan',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '999999',
            'prodi_mhs'     => 'Teknik Informatika',
            'alamat_rumah'  => 'Jl. Test TASMA Baru',
            'kelas_pondok'  => 'B/3',
            'tanggal'       => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/sktkp/' . $data->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_tasma_kkn_ppl', [
            'id'           => $data->id,
            'nama_lengkap' => 'After Update',
            'nomor_surat'  => $formatted,
            'alamat_rumah' => 'Jl. Test TASMA Baru',
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'       => '999-UPD',
            'nomor_surat' => $formatted,
            'nama_surat'  => 'Surat Keterangan TASMA, KKN, PPL',
            'user_id'     => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $data = SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Update',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '999999',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test TASMA',
            'kelas_pondok' => 'B/3',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $payload = ['nama_mhs' => '']; // Missing all required fields

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/sktkp/' . $data->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'no_surat', 'nama_mhs', 'tempat_lahir', 'tanggal_lahir',
                     'nim', 'prodi_mhs', 'alamat_rumah', 'kelas_pondok', 'tanggal'
                 ]);
    }

    public function test_update_returns_not_found_if_record_does_not_exist()
    {
        // update() returns 200 with status=>false when not found (no HTTP 404 status code set)
        $payload = [
            'prodi_id'      => $this->prodi->id,
            'no_surat'      => '100',
            'nama_mhs'      => 'No One',
            'tempat_lahir'  => 'Surabaya',
            'tanggal_lahir' => '2000-05-05',
            'nim'           => '000000',
            'prodi_mhs'     => 'Teknik Informatika',
            'alamat_rumah'  => 'Jl. No Where',
            'kelas_pondok'  => 'A/1',
            'tanggal'       => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/sktkp/999', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_daftar_tasma_kkn_ppl()
    {
        $data = SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_lengkap' => 'Before Delete',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '999999',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test TASMA',
            'kelas_pondok' => 'B/3',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/sktkp/' . $data->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_tasma_kkn_ppl', ['id' => $data->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/sktkp/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── DOWNLOAD PDF ─────────────────────────────────────────────────────────

    public function test_download_pdf_returns_pdf()
    {
        Storage::fake('google');
        Queue::fake();

        $data = SuratKeteranganTasmaKknPpl::forceCreate([
            'nomor_surat'  => 'SU-001/UII.085/TI/TL.00/05/2026',
            'ketua'        => 'Ketua TASMA TTD',
            'tanda_tangan_id' => $this->tandaTanganStaff->id,
            'nama_lengkap' => 'Download TASMA',
            'tempat_lahir' => 'Pasuruan',
            'tanggal_lahir'=> '2000-01-01',
            'nim'          => '333444555',
            'prodi_id'     => $this->prodi->id,
            'user_id'      => $this->user->id,
            'prodi_mhs'    => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test TASMA',
            'kelas_pondok' => 'B/3',
            'tanggal'      => '2026-05-26',
            'jenis_kelamin'=> 'L',
            'status'       => 'pending'
        ]);

        $response = $this->get('/api/sktkp/download-pdf/' . $data->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'inline; filename="surat_keterangan_tasma_kkn_ppl_333444555.pdf"',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_download_pdf_returns_404_if_not_found()
    {
        $response = $this->get('/api/sktkp/download-pdf/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
