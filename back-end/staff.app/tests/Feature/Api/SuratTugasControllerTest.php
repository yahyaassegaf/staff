<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Prodi;
use App\Models\JenisSurat;
use App\Models\SuratTugas;
use App\Models\TandaTangan;
use App\Models\NoSurat;
use App\Models\LogSurat;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SuratTugasControllerTest extends TestCase
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

        // 10. Create surat_tugas table
        Schema::create('surat_tugas', function ($table) {
            $table->id();
            $table->string('nomor');
            $table->string('nama_dosen');
            $table->string('alamat_dosen');
            $table->string('tugas_dosen');
            $table->text('tugasnya');
            $table->string('nama_mhs');
            $table->string('nim_nik');
            $table->string('fakultas_prodi');
            $table->text('judul_skripsi');
            $table->string('masa_penugasan');
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

        // Populate dependencies
        DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->tandaTangan = TandaTangan::create([
            'nama'   => 'Ketua Prodi TTD',
            'gambar' => 'img/ketua_prodi_ttd.png'
        ]);

        $this->prodi = Prodi::create([
            'id'              => 10,
            'nama'            => 'Teknik Informatika',
            'alias'           => 'TI',
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
            'nama'         => 'Surat Tugas',
            'alias'        => 'ST',
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

    public function test_index_returns_paginated_daftar_surat_tugas()
    {
        SuratTugas::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen A',
            'alamat_dosen'   => 'Alamat Dosen A',
            'tugas_dosen'    => 'Membimbing',
            'tugasnya'       => 'Membimbing Skripsi',
            'nama_mhs'       => 'Mhs ST Laki',
            'nim_nik'        => '333444555',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Judul A',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-tugas');

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('333444555', $data[0]['nim_nik']);
        $this->assertEquals('Teknik Informatika', $data[0]['nama_prodi']);
    }

    public function test_index_can_search_daftar_surat_tugas()
    {
        SuratTugas::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen A',
            'alamat_dosen'   => 'Alamat Dosen A',
            'tugas_dosen'    => 'Membimbing',
            'tugasnya'       => 'Membimbing Skripsi',
            'nama_mhs'       => 'Ahmad ST',
            'nim_nik'        => '111222333',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Judul A',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        SuratTugas::forceCreate([
            'nomor'          => 'SU-002/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen Budi',
            'alamat_dosen'   => 'Alamat Dosen B',
            'tugas_dosen'    => 'Membimbing',
            'tugasnya'       => 'Membimbing Skripsi',
            'nama_mhs'       => 'Budi ST',
            'nim_nik'        => '444555666',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Judul B',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-tugas?search=Budi');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi ST', $data[0]['nama_mhs']);
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_store_creates_daftar_surat_tugas_with_valid_data_and_logs_audit_trail()
    {
        $payload = [
            'prodi_id'       => $this->prodi->id,
            'no_surat'       => '789',
            'nama_dosen'     => 'Dr. Dosen',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'Galih ST',
            'nim_nik'        => '999888777',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi Galih',
            'masa_penugasan' => '2026-05-26', // note: required|date
            'tanggal'        => '2026-05-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-tugas', $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil ditambahkan'
                 ]);

        $formatted = 'SU-789/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_tugas', [
            'nim_nik'        => '999888777',
            'nomor'          => $formatted,
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'nama_dosen'     => 'Dr. Dosen',
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '789',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'      => '789',
            'nomor_surat'=> $formatted,
            'nama_surat' => 'Surat Tugas',
            'user_id'    => $this->user->id
        ]);
    }

    public function test_store_fails_with_invalid_data()
    {
        $payload = ['nama_mhs' => 'Missing Fields'];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/surat-tugas', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors([
                     'prodi_id', 'no_surat', 'nama_dosen', 'alamat_dosen',
                     'tugas_dosen', 'tugasnya', 'nim_nik', 'fakultas_prodi',
                     'judul_skripsi', 'masa_penugasan', 'tanggal'
                 ]);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_show_returns_daftar_surat_tugas()
    {
        $record = SuratTugas::forceCreate([
            'nomor'          => 'SU-123/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen Show',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'Show ST',
            'nim_nik'        => '111222333',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi Show',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-tugas/' . $record->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diambil'
                 ]);

        $this->assertEquals('Show ST', $response->json('data.nama_mhs'));
        $this->assertEquals('123', $response->json('data.no_surat')); // Extracted from SU-123/...
    }

    public function test_show_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/surat-tugas/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_update_modifies_daftar_surat_tugas_and_logs_audit_trail_when_new_no_surat()
    {
        Storage::fake('google');
        Queue::fake();

        $record = SuratTugas::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen Before',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'Before Update',
            'nim_nik'        => '999999',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi Before',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $payload = [
            'prodi_id'       => $this->prodi->id,
            'no_surat'       => '999-UPD',
            'nama_dosen'     => 'Dosen After',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'After Update',
            'nim_nik'        => '999999',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi After',
            'masa_penugasan' => '1 Semester', // Note: string in update(), unlike store() which is date
            'tanggal'        => '2026-05-26',
            'jenis_kelamin'  => 'L',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-tugas/' . $record->id, $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil diupdate'
                 ]);

        $formatted = 'SU-999-UPD/UII.085/TI/TL.00/05/2026';
        $this->assertDatabaseHas('surat_tugas', [
            'id'             => $record->id,
            'nama_mhs'       => 'After Update',
            'nama_dosen'     => 'Dosen After',
            'nomor'          => $formatted,
        ]);

        $this->assertDatabaseHas('nomor', [
            'nomor'   => '999-UPD',
            'user_id' => $this->user->id
        ]);

        $this->assertDatabaseHas('log_surat', [
            'nomor'      => '999-UPD',
            'nomor_surat'=> $formatted,
            'nama_surat' => 'Surat Tugas',
            'user_id'    => $this->user->id
        ]);
    }

    public function test_update_fails_with_invalid_data()
    {
        $record = SuratTugas::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen Before',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'Before Update',
            'nim_nik'        => '999999',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi Before',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $payload = ['nama_mhs' => '']; // Missing all required fields

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-tugas/' . $record->id, $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'no_surat', 'prodi_id', 'nama_dosen', 'alamat_dosen',
                     'tugas_dosen', 'tugasnya', 'nama_mhs', 'nim_nik',
                     'fakultas_prodi', 'judul_skripsi', 'masa_penugasan',
                     'tanggal', 'jenis_kelamin'
                 ]);
    }

    public function test_update_returns_not_found_if_record_does_not_exist()
    {
        $payload = [
            'prodi_id'       => $this->prodi->id,
            'no_surat'       => '100',
            'nama_dosen'     => 'No One',
            'alamat_dosen'   => 'Jl. Nowhere',
            'tugas_dosen'    => 'None',
            'tugasnya'       => 'Nothing',
            'nama_mhs'       => 'No One Mhs',
            'nim_nik'        => '000000',
            'fakultas_prodi' => 'None / None',
            'judul_skripsi'  => 'None',
            'masa_penugasan' => 'None',
            'tanggal'        => '2026-05-26',
            'jenis_kelamin'  => 'L',
        ];

        // controller returns 404 HTTP status for update if not found!
        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/surat-tugas/999', $payload);

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }

    // ─── DESTROY ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_daftar_surat_tugas()
    {
        $record = SuratTugas::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen Delete',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'Before Delete',
            'nim_nik'        => '999999',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi Delete',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-tugas/' . $record->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => true,
                     'message' => 'Data berhasil dihapus'
                 ]);

        $this->assertDatabaseMissing('surat_tugas', ['id' => $record->id]);
    }

    public function test_destroy_returns_404_if_not_found()
    {
        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/surat-tugas/999');

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

        $record = SuratTugas::forceCreate([
            'nomor'          => 'SU-001/UII.085/TI/TL.00/05/2026',
            'nama_dosen'     => 'Dosen PDF',
            'alamat_dosen'   => 'Jl. Dosen',
            'tugas_dosen'    => 'Penguji',
            'tugasnya'       => 'Menguji Skripsi',
            'nama_mhs'       => 'Download ST',
            'nim_nik'        => '333444555',
            'fakultas_prodi' => 'Teknik / TI',
            'judul_skripsi'  => 'Skripsi PDF',
            'masa_penugasan' => '1 Semester',
            'tanggal'        => '2026-05-26',
            'prodi_id'       => $this->prodi->id,
            'user_id'        => $this->user->id,
            'jenis_kelamin'  => 'L',
            'status'         => 'pending'
        ]);

        $response = $this->get('/api/surat-tugas/download-pdf/' . $record->id);

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString(
            'inline; filename="surat_tugas_333444555.pdf"',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_download_pdf_returns_404_if_not_found()
    {
        $response = $this->get('/api/surat-tugas/download-pdf/999');

        $response->assertStatus(404)
                 ->assertJson([
                     'status'  => false,
                     'message' => 'Data tidak ditemukan'
                 ]);
    }
}
