<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            ProdiSeeder::class,
        ]);
        $level = Level::create([
            'nama' => 'staff',
        ]);

        $level = Level::create([
            'nama' => 'admin',
        ]);

        User::factory()->create([
            'name'     => 'prodipba',
            'username' => 'prodipba',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'prodi_id' => 10,
            'email'    => 'prodipba@example.com',
        ]);
        User::factory()->create([
            'name'     => 'prodihki',
            'username' => 'prodihki',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email'    => 'prodihki@example.com',
        ]);
        User::factory()->create([
            'name'     => 'prodimhu',
            'username' => 'prodimhu',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email'    => 'prodimhu@example.com',
        ]);
        User::factory()->create([
            'name'     => 'prodiesy',
            'username' => 'prodiesy',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email'    => 'prodiesy@example.com',
        ]);
        User::factory()->create([
            'name'     => 'prodimpi',
            'username' => 'prodimpi',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email'    => 'prodimpi@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodikpi',
            'username' => 'prodikpi',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodikpi@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodispa',
            'username' => 'prodispa',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodispa@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodikpk',
            'username' => 'prodikpk',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodikpk@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodimtk',
            'username' => 'prodimtk',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodimtk@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodipai',
            'username' => 'prodipai',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodipai@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodibki',
            'username' => 'prodibki',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodispi@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodiipa',
            'username' => 'prodiipa',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodiipa@example.com',
        ]);
        User::factory()->create([
            'name' => 'prodiips',
            'username' => 'prodiips',
            'password' => Hash::make('123456'),
            'level_id' => 1,
            'email' => 'prodiips@example.com',
        ]);
    }
}
