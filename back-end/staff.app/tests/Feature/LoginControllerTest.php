<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    private $level;
    private $prodi;
    private $user;
    private $password = 'secret123';

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create necessary supporting level and prodi records
        $this->level = Level::create([
            'id' => 1,
            'nama' => 'staff'
        ]);

        $this->prodi = Prodi::create([
            'id' => 10,
            'nama' => 'Pendidikan Bahasa Arab',
            'alias' => 'PBA',
            'nama_kepala' => 'Dr. Ahmad',
            'nidn_kepala' => '12345678'
        ]);

        // 2. Create default user
        $this->user = User::create([
            'name' => 'prodipba',
            'username' => 'prodipba',
            'password' => Hash::make($this->password),
            'level_id' => $this->level->id,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'email' => 'prodipba@example.com',
            'phone' => '08123456789'
        ]);
    }

    /**
     * Test successful user login
     */
    public function test_login_successful()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'prodipba',
            'password' => $this->password
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Login berhasil'
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'token',
                'user'
            ]);
    }

    /**
     * Test login validation failure (missing credentials)
     */
    public function test_login_validation_fails()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJson([
                'status' => false,
                'message' => 'Validasi gagal'
            ])
            ->assertJsonStructure(['errors']);
    }

    /**
     * Test login with incorrect username
     */
    public function test_login_incorrect_username()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'nonexistentuser',
            'password' => $this->password
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Username salah'
            ]);
    }

    /**
     * Test login with incorrect password
     */
    public function test_login_incorrect_password()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'prodipba',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Username atau password salah'
            ]);
    }

    /**
     * Test viewing profile
     */
    public function test_view_profile()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Profile berhasil diambil'
            ])
            ->assertJsonStructure(['user']);
    }

    /**
     * Test updating user profile
     */
    public function test_update_profile()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/profile', [
                '_method' => 'PUT',
                'name' => 'prodipba updated',
                'username' => 'prodipba_updated',
                'handphone' => '0899999999',
                'email' => 'updated@example.com',
                'password' => 'newpassword123',
                'foto' => $file
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Profile berhasil diupdate'
            ]);

        // Assert user updated in database
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'prodipba updated',
            'username' => 'prodipba_updated',
            'email' => 'updated@example.com'
        ]);
    }

    /**
     * Test user logout
     */
    public function test_logout()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Logout berhasil'
            ]);
    }

    /**
     * Test dataUsers listing
     */
    public function test_data_users()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/data-users');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Test show single user
     */
    public function test_show_user()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/data-users/{$this->user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diambil'
            ])
            ->assertJsonStructure(['user']);
    }

    /**
     * Test getLevel list
     */
    public function test_get_levels()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/get-level');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Test getProdi list
     */
    public function test_get_prodis()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/get-prodi');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Test getTandaTangan list
     */
    public function test_get_tanda_tangan()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/get-all-tanda-tangan');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true
            ])
            ->assertJsonStructure(['data']);
    }

    /**
     * Test create a new user (store)
     */
    public function test_store_user()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('foto_mhs.png');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/data-users', [
                'name' => 'New User',
                'handphone' => '0877777777',
                'email' => 'newuser@example.com',
                'password' => 'userpassword',
                'prodi_id' => $this->prodi->id,
                'foto' => $file,
                'level_id' => $this->level->id,
                'jenis_kelamin' => 'L'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com'
        ]);
    }

    /**
     * Test update user
     */
    public function test_update_user()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('new_foto.png');

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/data-users/{$this->user->id}", [
                'name' => 'User Updated Name',
                'handphone' => '08888888888',
                'email' => 'updated_email@example.com',
                'level_id' => $this->level->id,
                'prodi_id' => $this->prodi->id,
                'password' => 'newpassword123',
                'foto' => $file,
                'jenis_kelamin' => 'P'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'User Updated Name',
            'email' => 'updated_email@example.com',
            'jenis_kelamin' => 'P'
        ]);
    }

    /**
     * Test delete user (destroy)
     */
    public function test_destroy_user()
    {
        // Create an extra user to delete so we don't delete the acting user
        $extraUser = User::create([
            'name' => 'Delete Me',
            'username' => 'deleteme',
            'password' => Hash::make('123456'),
            'level_id' => $this->level->id,
            'prodi_id' => $this->prodi->id,
            'jenis_kelamin' => 'L',
            'email' => 'delete@example.com',
            'phone' => '0000'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/data-users/{$extraUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $extraUser->id
        ]);
    }
}
