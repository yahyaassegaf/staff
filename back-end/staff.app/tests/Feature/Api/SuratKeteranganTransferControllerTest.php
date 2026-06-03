<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratKeteranganTransfer;
use App\Models\TandaTangan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratKeteranganTransferControllerTest extends TestCase
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

        // 7. Create th_akademik table (referenced by PDF join)
        Schema::create('th_akademik', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('kode')->nullable();
            $table->string('semester')->nullable();
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

        // 11. Create surat_keterangan_transfer table (combined base + addendum migrations)
        Schema::create('surat_keterangan_transfer', function ($table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir')->nullable();
            $table->string('nim');
            $table->string('jurusan_prodi');
            $table->string('semester')->nullable();
            $table->string('tahun_akademik')->nullable();
            $table->text('alamat')->nullable();
            $table->string('universitas_tujuan')->nullable();
            $table->date('tanggal');
            $table->integer('user_id');
            $table->integer('prodi_id');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('local_path')->nullable();
            $table->string('drive_link')->nullable();
            $table->enum('status', ['pending', 'uploaded', 'failed'])->default('pending');
            $table->timestamps();
        });

        // Populate basic dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->tandaTangan = TandaTangan::create([
            'nama'   => 'Dekan FT TTD',
            'gambar' => 'img/dekan_ttd.png'
        ]);

        $this->prodi = Prodi::create([
            'id'             => 10,
            'nama'           => 'Teknik Informatika',
            'alias'          => 'TI',
            'nama_kepala'    => 'Dr. H. Ahmad',
            'nidn_kepala'    => '12345678',
            'tanda_tangan_id'=> $this->tandaTangan->id
        ]);

        $this->user = User::factory()->create([
            'level_id'      => 1,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);

        JenisSurat::create([
            'nama'         => 'Surat Keterangan Transfer',
            'alias'        => 'SKM',
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

    public function test_index_returns_paginated_daftar_transfer()
    {
        SuratKeteranganTransfer::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama'           => 'Mhs Transfer Laki',
            'tanggal_lahir'  => '2000-01-01',
            'tempat_lahir'   => 'Pasuruan',
            'nim'            => '333444555',
            'jurusan_prodi'  => 'Teknik Informatika',
            'semester'       => 'IX (Sembilan)',
            'tahun_akademik' => '2025/2026',
            'tanggal'        => '2026-05-26',
            'user_id'        => $this->user->id,
            'prodi_id'       => $this->prodi->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan-transfer');

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim']);
    }

    public function test_index_can_search_daftar_transfer()
    {
        SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Ahmad Transfer',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '111222333',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Budi Transfer',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '444555666',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan-transfer?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi Transfer', $data[0]['nama']);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_creates_daftar_transfer_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id'      => $this->prodi->id,
            'no_surat'      => '789',
            'nama'          => 'Galih Transfer',
            'tanggal_lahir' => '2001-02-03',
            'tempat_lahir'  => 'Bangil',
            'nim'           => '999888777',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'alamat'        => 'Jl. Kenanga No. 5',
            'universitas_tujuan' => 'Universitas Lain',
            'tanggal'       => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-keterangan-transfer', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $formatted = 'SU-789/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_transfer', [
            'nim'    => '999888777',
            'nomor'  => $formatted,
            'prodi_id' => $this->prodi->id,
            'user_id'  => $this->user->id,
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '789',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'      => '789',
            'nomor_surat'=> $formatted,
            'nama_surat' => 'Surat Keterangan Transfer',
            'user_id'    => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = ['nama' => 'Missing Fields'];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-keterangan-transfer', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors([
                     'prodi_id', 'no_surat', 'tanggal_lahir', 'nim',
                     'jurusan_prodi', 'tanggal'
                 ]);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_returns_daftar_transfer()
    {
        $record = SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-123/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Show Transfer',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '111222333',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan-transfer/' . $record->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $this->assertEquals('Show Transfer', $response->json('data.nama'));
        $this->assertEquals('123', $response->json('data.no_surat')); // Extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-keterangan-transfer/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_modifies_daftar_transfer_and_logs_audit_trail_when_new_no_surat()
    {
        Storage::fake('google');
        Queue::fake();

        $record = SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Before Update',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '999999',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        $payload = [
            'prodi_id'      => $this->prodi->id,
            'no_surat'      => '999-UPD',
            'nama'          => 'After Update',
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir'  => 'Pasuruan',
            'nim'           => '999999',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'alamat'        => 'Jl. Test Baru',
            'universitas_tujuan' => 'Universitas Tujuan Baru',
            'tanggal'       => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-keterangan-transfer/' . $record->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_keterangan_transfer', [
            'id'    => $record->id,
            'nama'  => 'After Update',
            'nomor' => $formatted,
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'      => '999-UPD',
            'nomor_surat'=> $formatted,
            'nama_surat' => 'Surat Keterangan Transfer',
            'user_id'    => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $record = SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Before Update',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '999999',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        $payload = ['nama' => '']; // Missing all required fields

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-keterangan-transfer/' . $record->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'prodi_id', 'no_surat', 'nama', 'tanggal_lahir', 'nim',
                     'jurusan_prodi', 'tanggal'
                 ]);
    }

    public function test_update_returns_404_if_not_found()
    {
        // update() returns HTTP 404 when not found
        $payload = [
            'prodi_id'      => $this->prodi->id,
            'no_surat'      => '100',
            'nama'          => 'No One',
            'tanggal_lahir' => '2000-05-05',
            'nim'           => '000000',
            'jurusan_prodi' => 'Teknik Informatika',
            'tanggal'       => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-keterangan-transfer/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_daftar_transfer()
    {
        $record = SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Before Delete',
            'tanggal_lahir' => '2000-01-01',
            'nim'           => '999999',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-keterangan-transfer/' . $record->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_keterangan_transfer', ['id' => $record->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-keterangan-transfer/999');

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

        $record = SuratKeteranganTransfer::forceCreate([
            'nomor'         => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama'          => 'Download Transfer',
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir'  => 'Pasuruan',
            'nim'           => '333444555',
            'jurusan_prodi' => 'Teknik Informatika',
            'semester'      => 'IX (Sembilan)',
            'tahun_akademik'=> '2025/2026',
            'alamat'        => 'Jl. Test Transfer',
            'tanggal'       => '2026-05-26',
            'user_id'       => $this->user->id,
            'prodi_id'      => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'status'        => 'pending'
        ]);

        $response = $this->get('/api/surat-keterangan-transfer/download-pdf/' . $record->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'inline; filename="surat_keterangan_transfer_333444555.pdf"',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_download_pdf_returns_404_if_not_found()
    {
        $response = $this->get('/api/surat-keterangan-transfer/download-pdf/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
