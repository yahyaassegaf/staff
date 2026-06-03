<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\SuratKeterangan6;
use App\Models\Prodi;

class Sk6ControllerTest extends TestCase
{
    protected $user;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Facades\Storage::fake('google');

        \Illuminate\Support\Facades\Schema::create('level', function ($table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
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

        \Illuminate\Support\Facades\Schema::create('prodi', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('kode')->nullable();
            $table->string('alias')->nullable();
            $table->string('nama_kepala')->nullable();
            $table->string('nidn_kepala')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('tanda_tangan', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->text('tdd')->nullable();
            $table->string('gambar')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('fakultas', function ($table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('dekan')->nullable();
            $table->string('nidn')->nullable();
            $table->string('nidn_dekan')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->string('alias')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('fakultas_prodi', function ($table) {
            $table->id();
            $table->unsignedBigInteger('fakultas_id');
            $table->unsignedBigInteger('prodi_id');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('setting_jabatan', function ($table) {
            $table->id();
            $table->string('kunci_jabatan')->unique();
            $table->string('nama_jabatan');
            $table->string('nidn')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('jenis_surat', function ($table) {
            $table->id();
            $table->string('alias')->nullable();
            $table->string('format_surat')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('nomor', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('log_surat', function ($table) {
            $table->id();
            $table->string('nomor')->nullable();
            $table->string('nomor_surat')->nullable();
            $table->string('nama_surat')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        $this->createSuratTables();

        \Illuminate\Support\Facades\DB::table('level')->insert(['id' => 1, 'nama' => 'Test Level', 'created_at' => now(), 'updated_at' => now()]);

        $this->prodi = Prodi::create(['nama' => 'Teknik Informatika', 'alias' => 'TI']);
        
        $fakultasId = \Illuminate\Support\Facades\DB::table('fakultas')->insertGetId([
            'nama' => 'Fakultas Teknik',
            'alias' => 'FT',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        \Illuminate\Support\Facades\DB::table('fakultas_prodi')->insert([
            'fakultas_id' => $fakultasId,
            'prodi_id' => $this->prodi->id,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Mock Setting Jabatan for Ketua Tasma & Ketua Qismul Aman
        $ttd1 = \App\Models\TandaTangan::create(['nama' => 'Ketua Tasma TTD']);
        \App\Models\SettingJabatan::create(['kunci_jabatan' => 'ketua_tasma', 'nama_jabatan' => 'Ketua Tasma', 'tanda_tangan_id' => $ttd1->id]);

        $ttd2 = \App\Models\TandaTangan::create(['nama' => 'Ketua QA TTD']);
        \App\Models\SettingJabatan::create(['kunci_jabatan' => 'ketua_qismul_aman', 'nama_jabatan' => 'Ketua QA', 'tanda_tangan_id' => $ttd2->id]);

        $this->user = User::factory()->create([
            'level_id' => 1, 
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L'
        ]);
    }

    private function createSuratTables()
    {
        $commonFields = function ($table) {
            $table->id();
            $table->string('nomor_surat')->nullable();
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nim')->nullable();
            $table->string('prodi_mhs')->nullable();
            $table->string('prodi_mahasiswa')->nullable(); // For sklmk differences
            $table->text('alamat_rumah')->nullable();
            $table->string('kelas_pondok')->nullable();
            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('status')->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('drive_link')->nullable();
            $table->string('local_path')->nullable();
            $table->unsignedBigInteger('tanda_tangan_id')->nullable();
            $table->timestamps();
        };

        \Illuminate\Support\Facades\Schema::create('surat_keterangan_lulus_mata_kuliah', $commonFields);
        \Illuminate\Support\Facades\Schema::create('surat_keterangan_administrasi_keuangan', $commonFields);
        \Illuminate\Support\Facades\Schema::create('surat_keterangan_ujian_komprehensif_diniyah', $commonFields);

        \Illuminate\Support\Facades\Schema::create('surat_keterangan_tasma_kkn_ppl', function ($table) use ($commonFields) {
            $commonFields($table);
            $table->string('ketua')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('surat_keterangan_qismul_aman', function ($table) use ($commonFields) {
            $commonFields($table);
            $table->string('ketua')->nullable();
            $table->date('tanggal_berlaku_dari')->nullable();
            $table->date('tanggal_berlaku_sampai')->nullable();
        });

        \Illuminate\Support\Facades\Schema::create('surat_keterangan_6', function ($table) {
            $table->id();
            $table->string('nama_mhs')->nullable();
            $table->string('nim')->nullable();
            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('prodi_id')->nullable();
            $table->unsignedBigInteger('surat_keterangan_lulus_mata_kuliah_id')->nullable();
            $table->unsignedBigInteger('surat_keterangan_administrasi_keuangan_id')->nullable();
            $table->unsignedBigInteger('surat_keterangan_tasma_kkn_ppl_id')->nullable();
            $table->unsignedBigInteger('surat_keterangan_ujian_komprehensif_diniyah_id')->nullable();
            $table->unsignedBigInteger('surat_keterangan_qismul_aman_id')->nullable();
            $table->string('drive_file_id')->nullable();
            $table->string('drive_link')->nullable();
            $table->string('status')->nullable();
            $table->string('local_path')->nullable();
            $table->timestamps();
        });
    }

    public function test_store_creates_sk6_and_its_5_children()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '123456789',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '001',
            'no_skak' => '002',
            'no_sktkp' => '003',
            'no_skqa' => '004',
            'no_skukd' => '005',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200)
                 ->assertJson(['status' => true]);

        // Check if header created
        $this->assertDatabaseHas('surat_keterangan_6', [
            'nama_mhs' => 'John Doe',
            'nim' => '123456789',
        ]);

        $sk6 = SuratKeterangan6::first();

        // Check 5 children
        $this->assertDatabaseHas('surat_keterangan_lulus_mata_kuliah', ['id' => $sk6->surat_keterangan_lulus_mata_kuliah_id, 'nama_lengkap' => 'John Doe']);
        $this->assertDatabaseHas('surat_keterangan_administrasi_keuangan', ['id' => $sk6->surat_keterangan_administrasi_keuangan_id, 'nama_lengkap' => 'John Doe']);
        $this->assertDatabaseHas('surat_keterangan_tasma_kkn_ppl', ['id' => $sk6->surat_keterangan_tasma_kkn_ppl_id, 'nama_lengkap' => 'John Doe', 'ketua' => 'Ketua Tasma TTD']);
        $this->assertDatabaseHas('surat_keterangan_qismul_aman', ['id' => $sk6->surat_keterangan_qismul_aman_id, 'nama_lengkap' => 'John Doe', 'ketua' => 'Ketua QA TTD']);
        $this->assertDatabaseHas('surat_keterangan_ujian_komprehensif_diniyah', ['id' => $sk6->surat_keterangan_ujian_komprehensif_diniyah_id, 'nama_lengkap' => 'John Doe']);

        // Check Log Surat for all 5 numbers
        $this->assertDatabaseHas('log_surat', ['nomor' => '001']);
        $this->assertDatabaseHas('log_surat', ['nomor' => '002']);
        $this->assertDatabaseHas('log_surat', ['nomor' => '003']);
        $this->assertDatabaseHas('log_surat', ['nomor' => '004']);
        $this->assertDatabaseHas('log_surat', ['nomor' => '005']);
    }

    public function test_store_fails_with_duplicate_numbers_in_request()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '123456789',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '999',
            'no_skak' => '999', // Duplicate!
            'no_sktkp' => '003',
            'no_skqa' => '004',
            'no_skukd' => '005',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['nomor_surat_skak']);
    }

    public function test_store_fails_when_formatted_nomor_surat_already_exists_in_database()
    {
        // Insert a record into database with a formatted nomor_surat
        \Illuminate\Support\Facades\DB::table('surat_keterangan_lulus_mata_kuliah')->insert([
            'nomor_surat' => 'SU-001/UII.085/TI/TL.00/' . date('m') . '/' . date('Y'),
            'prodi_id' => $this->prodi->id,
            'nama_lengkap' => 'Existing Student',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '999999999',
            'prodi_mahasiswa' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'user_id' => $this->user->id,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '123456789',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '001', // This will generate the same formatted nomor_surat!
            'no_skak' => '002',
            'no_sktkp' => '003',
            'no_skqa' => '004',
            'no_skukd' => '005',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        // Ensure JenisSurat format is registered
        \App\Models\JenisSurat::create([
            'alias' => 'SKLM',
            'format_surat' => 'SU-{NO}/UII.085/{PRODI}/TL.00/{BULAN}/{TAHUN}',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);

        $response->assertStatus(422)
                 ->assertJson([
                     'status' => false,
                     'message' => 'Validasi gagal'
                 ])
                 ->assertJsonValidationErrors(['nomor_surat_sklmk']);
    }

    public function test_index_returns_sk6_data()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '123456789',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '001',
            'no_skak' => '002',
            'no_sktkp' => '003',
            'no_skqa' => '004',
            'no_skukd' => '005',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/sk6');

        $response->assertStatus(200);
        $data = $response->json('data.data');
        $this->assertCount(1, $data);
        $this->assertEquals('John Doe', $data[0]['nama_lengkap']);
    }

    public function test_show_returns_detail_sk6()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => '123456789',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '001',
            'no_skak' => '002',
            'no_sktkp' => '003',
            'no_skqa' => '004',
            'no_skukd' => '005',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);
        $sk6 = SuratKeterangan6::first();

        $response = $this->actingAs($this->user, 'sanctum')->getJson('/api/sk6/' . $sk6->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Data berhasil diambil'
                 ]);
        
        $this->assertEquals('John Doe', $response->json('data.nama_mhs'));
    }

    public function test_destroy_deletes_sk6_and_children_and_logs()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe Delete',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'DEL123',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '901',
            'no_skak' => '902',
            'no_sktkp' => '903',
            'no_skqa' => '904',
            'no_skukd' => '905',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);
        $sk6 = SuratKeterangan6::where('nim', 'DEL123')->first();
        $this->assertNotNull($sk6);

        $response = $this->actingAs($this->user, 'sanctum')->deleteJson('/api/sk6/' . $sk6->id);

        $response->assertStatus(200);

        // Header deleted
        $this->assertDatabaseMissing('surat_keterangan_6', ['id' => $sk6->id]);

        // Children deleted
        $this->assertDatabaseMissing('surat_keterangan_lulus_mata_kuliah', ['id' => $sk6->surat_keterangan_lulus_mata_kuliah_id]);
        $this->assertDatabaseMissing('surat_keterangan_administrasi_keuangan', ['id' => $sk6->surat_keterangan_administrasi_keuangan_id]);
        
        // Logs dipertahankan
        $this->assertDatabaseHas('log_surat', ['nomor' => '901']);
        $this->assertDatabaseHas('nomor', ['nomor' => '901']);
    }

    public function test_update_modifies_sk6_and_children_and_logs()
    {
        $payload = [
            'prodi_id' => $this->prodi->id,
            'nama_mhs' => 'John Doe',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'nim' => 'UPD123',
            'prodi_mhs' => 'Teknik Informatika',
            'alamat_rumah' => 'Jl. Test No. 1',
            'kelas_pondok' => 'Kelas A',
            'tanggal' => date('Y-m-d'),
            'no_sklmk' => '501',
            'no_skak' => '502',
            'no_sktkp' => '503',
            'no_skqa' => '504',
            'no_skukd' => '505',
            'tanggal_berlaku_dari' => '2026-05-26',
            'tanggal_berlaku_sampai' => '2026-06-26',
        ];

        // 1. Create first
        $this->actingAs($this->user, 'sanctum')->postJson('/api/sk6', $payload);
        $sk6 = SuratKeterangan6::where('nim', 'UPD123')->first();

        // 2. Update with new numbers
        $payload['nama_mhs'] = 'John Doe Updated';
        $payload['no_sklmk'] = '501-UPD';
        $payload['no_skak'] = '502-UPD';
        $payload['no_sktkp'] = '503-UPD';
        $payload['no_skqa'] = '504-UPD';
        $payload['no_skukd'] = '505-UPD';

        $response = $this->actingAs($this->user, 'sanctum')->putJson('/api/sk6/' . $sk6->id, $payload);
        
        $response->assertStatus(200);

        // Header check
        $this->assertDatabaseHas('surat_keterangan_6', [
            'id' => $sk6->id,
            'nama_mhs' => 'John Doe Updated',
        ]);

        // Children check
        $this->assertDatabaseHas('surat_keterangan_lulus_mata_kuliah', [
            'id' => $sk6->surat_keterangan_lulus_mata_kuliah_id, 
            'nama_lengkap' => 'John Doe Updated'
        ]);

        // Logs check (Ensure the newly submitted numbers are registered)
        $this->assertDatabaseHas('nomor', ['nomor' => '501-UPD']);
        $this->assertDatabaseHas('log_surat', ['nomor' => '501-UPD']);
        $this->assertDatabaseHas('nomor', ['nomor' => '502-UPD']);
        $this->assertDatabaseHas('log_surat', ['nomor' => '502-UPD']);
    }
    
    // Skipping Download PDF test because it involves executing
    // Job dispatching and heavy PDF rendering components which can fail in a stripped down testing environment.
}
