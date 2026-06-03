<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratPernyataanVerifikasiNilai;
use App\Models\TandaTangan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratPernyataanVerifikasiNilaiControllerTest extends TestCase
{
    protected $user;
    protected $prodi;
    protected $tandaTangan;
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
            $table->string('alias')->nullable(); // REQUIRED: store/update resolves fakultas by prodi.alias
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

        // 10. Create surat_pernyataan_verifikasi_nilai table
        Schema::create('surat_pernyataan_verifikasi_nilai', function ($table) {
            $table->id();
            $table->string('nomor');
            $table->string('niy');
            $table->string('jabatan');
            $table->string('nama_mahasiswa');
            $table->string('nim');
            $table->string('prodi')->nullable();     // used by index search: surat_pernyataan_verifikasi_nilai.prodi
            $table->string('prodi_mhs')->nullable(); // stored via $surat->prodi_mhs = $validate['prodi']
            $table->string('fakultas');
            $table->date('tanggal');
            $table->integer('prodi_id');
            $table->integer('user_id');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->integer('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        // Populate dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->tandaTangan = TandaTangan::create([
            'nama'   => 'Ketua Prodi TTD',
            'gambar' => 'img/ketua_prodi_ttd.png'
        ]);

        $this->prodi = Prodi::create([
            'id'              => 10,
            'nama'            => 'Teknik Informatika',
            'alias'           => 'TI',   // CRITICAL: store/update resolves fakultas by alias
            'nama_kepala'     => 'Dr. H. Ahmad',
            'nidn_kepala'     => '12345678',
            'tanda_tangan_id' => $this->tandaTangan->id
        ]);

        $this->user = User::factory()->create([
            'level_id'      => 1,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama'         => 'Surat Pernyataan Verifikasi Nilai',
            'alias'        => 'SPMVN',
            'format_surat' => 'SU-{NO}/UII.085/TI/TL.00/{BULAN}/{TAHUN}'
        ]);

        $this->fakultas = DB::table('fakultas')->insertGetId([
            'nama'            => 'Teknik',
            'dekan'           => 'Dekan Teknik',
            'nidn'            => '88888888',
            'nidn_dekan'      => '88888888',
            'tanda_tangan_id' => $this->tandaTangan->id,
            'alias'           => 'FT',
            'created_at'      => now(),
            'updated_at'      => now()
        ]);

        DB::table('fakultas_prodi')->insert([
            'fakultas_id' => $this->fakultas,
            'prodi_id'    => $this->prodi->id,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────

    public function test_index_returns_paginated_daftar_spvn()
    {
        SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi Teknik Informatika',
            'nama_mahasiswa' => 'Mhs SPVN Laki',
            'nim'            => '333444555',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spvn');

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim']);
    }

    public function test_index_can_search_daftar_spvn()
    {
        SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi TI',
            'nama_mahasiswa' => 'Ahmad SPVN',
            'nim'            => '111222333',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-002/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi TI',
            'nama_mahasiswa' => 'Budi SPVN',
            'nim'            => '444555666',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spvn?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi SPVN', $data[0]['nama_mahasiswa']);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_creates_daftar_spvn_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '789',
            'nama_mhs' => 'Galih SPVN',
            'nim'      => '999888777',
            'prodi'    => 'Teknik Informatika',
            'tanggal'  => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/spvn', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $formatted = 'SU-789/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_pernyataan_verifikasi_nilai', [
            'nim'             => '999888777',
            'nomor'           => $formatted,
            'prodi_id'        => $this->prodi->id,
            'user_id'         => $this->user->id,
            'jabatan'         => 'Ketua Program Studi Teknik Informatika',
            'niy'             => '12345678',
            'fakultas'        => 'Teknik', // resolved via prodi.alias -> fakultas_prodi join
            'tanda_tangan_id' => $this->tandaTangan->id,
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '789',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'      => '789',
            'nomor_surat'=> $formatted,
            'nama_surat' => 'Surat Pernyataan Verifikasi Nilai',
            'user_id'    => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = ['nama_mhs' => 'Missing Fields'];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/spvn', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors([
                     'prodi_id', 'no_surat', 'nim', 'prodi', 'tanggal'
                 ]);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_returns_daftar_spvn()
    {
        $record = SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-123/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi TI',
            'nama_mahasiswa' => 'Show SPVN',
            'nim'            => '111222333',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spvn/' . $record->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $this->assertEquals('Show SPVN', $response->json('data.nama_mahasiswa'));
        $this->assertEquals('123', $response->json('data.no_surat')); // extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/spvn/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_modifies_daftar_spvn_and_logs_audit_trail_when_new_no_surat()
    {
        Storage::fake('google');
        Queue::fake();

        $record = SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi TI',
            'nama_mahasiswa' => 'Before Update',
            'nim'            => '999999',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '999-UPD',
            'nama_mhs' => 'After Update',
            'nim'      => '999999',
            'prodi'    => 'Teknik Informatika',
            'tanggal'  => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/spvn/' . $record->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_pernyataan_verifikasi_nilai', [
            'id'             => $record->id,
            'nama_mahasiswa' => 'After Update',
            'nomor'          => $formatted,
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'      => '999-UPD',
            'nomor_surat'=> $formatted,
            'nama_surat' => 'Surat Pernyataan Verifikasi Nilai',
            'user_id'    => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $record = SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi TI',
            'nama_mahasiswa' => 'Before Update',
            'nim'            => '999999',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $payload = ['nama_mhs' => '']; // Missing all required fields

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/spvn/' . $record->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'no_surat', 'prodi_id', 'nama_mhs', 'nim', 'prodi', 'tanggal'
                 ]);
    }

    public function test_update_returns_not_found_if_record_does_not_exist()
    {
        // update() returns HTTP 200 with status=>false when not found (no HTTP 404 status code set)
        $payload = [
            'prodi_id' => $this->prodi->id,
            'no_surat' => '100',
            'nama_mhs' => 'No One',
            'nim'      => '000000',
            'prodi'    => 'Teknik Informatika',
            'tanggal'  => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/spvn/999', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_daftar_spvn()
    {
        $record = SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'niy'            => '12345678',
            'jabatan'        => 'Ketua Program Studi TI',
            'nama_mahasiswa' => 'Before Delete',
            'nim'            => '999999',
            'prodi_mhs'      => 'Teknik Informatika',
            'fakultas'       => 'Teknik',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/spvn/' . $record->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_pernyataan_verifikasi_nilai', ['id' => $record->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/spvn/999');

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

        $record = SuratPernyataanVerifikasiNilai::forceCreate([
            'nomor'           => 'SU-001/UII.085/TI/TL.00/05/2026',
            'niy'             => '12345678',
            'jabatan'         => 'Ketua Program Studi TI',
            'nama_mahasiswa'  => 'Download SPVN',
            'nim'             => '333444555',
            'prodi_mhs'       => 'Teknik Informatika',
            'fakultas'        => 'Teknik',
            'tanggal'         => '2026-05-26',
            'prodi_id'        => $this->prodi->id,
            'user_id'         => $this->user->id,
            'tanda_tangan_id' => $this->tandaTangan->id,
            'jenis_kelamin'   => 'L',
            'status'          => 'pending'
        ]);

        $response = $this->get('/api/spvn/download-pdf/' . $record->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'inline; filename="surat_pernyataan_verifikasi_nilai_333444555.pdf"',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_download_pdf_returns_404_if_not_found()
    {
        $response = $this->get('/api/spvn/download-pdf/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
