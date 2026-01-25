<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TandaTanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('tanda_tangan')->insert([
            [
                'id' => 1,
                'nama' => 'Dr. Mustahar Ali Wardana, M.Pd.',
                'tdd' => null,
                'gambar' => 'tdd/1767085145_aliwardana.png',
                'user_id' => 1,
                'created_at' => '2025-12-30 01:59:05',
                'updated_at' => '2025-12-30 01:59:05',
            ],
            [
                'id' => 2,
                'nama' => 'Reiza Praselanova, M.I.Kom',
                'tdd' => null,
                'gambar' => 'tdd/1767085345_Reiza Praselanova, M.I.Kom.png',
                'user_id' => 1,
                'created_at' => '2025-12-30 02:02:25',
                'updated_at' => '2025-12-30 02:02:25',
            ],
            [
                'id' => 3,
                'nama' => 'Dr. Achmad Sulton M.Pd',
                'tdd' => null,
                'gambar' => 'tdd/1767087061_Dr. Achmad Sulton M.Pd .png',
                'user_id' => 1,
                'created_at' => '2025-12-30 02:31:01',
                'updated_at' => '2025-12-30 02:31:01',
            ],
            [
                'id' => 4,
                'nama' => 'Ahmad Misbah, M.Pd.',
                'tdd' => null,
                'gambar' => 'tdd/1767088088_Ahmad Misbah, M.Pd.png',
                'user_id' => 1,
                'created_at' => '2025-12-30 02:48:08',
                'updated_at' => '2025-12-30 02:48:08',
            ],
            [
                'id' => 5,
                'nama' => 'samsul',
                'tdd' => null,
                'gambar' => 'tdd/1767089801_samsul.png',
                'user_id' => 1,
                'created_at' => '2025-12-30 03:16:41',
                'updated_at' => '2025-12-30 03:16:41',
            ],
            [
                'id' => 6,
                'nama' => 'M Robi’in, M.M',
                'tdd' => null,
                'gambar' => 'tdd/1767089953_M Robi’in, M.M.png',
                'user_id' => 1,
                'created_at' => '2025-12-30 03:19:13',
                'updated_at' => '2025-12-30 03:19:13',
            ],
            [
                'id' => 7,
                'nama' => 'Mohamad Syafiq, S.Psi, M.Pd',
                'tdd' => null,
                'gambar' => 'tdd/1767090177_Mohamad Syafiq, S.Psi, M.Pd.jpeg',
                'user_id' => 1,
                'created_at' => '2025-12-30 03:22:57',
                'updated_at' => '2025-12-30 03:22:57',
            ],
            [
                'id' => 8,
                'nama' => 'Dr. Muhammad Zuhdi, M.H.I.',
                'tdd' => null,
                'gambar' => 'tdd/1767090510_Dr. Muhammad Zuhdi, M.H.I.png',
                'user_id' => 1,
                'created_at' => '2025-12-30 03:28:30',
                'updated_at' => '2025-12-30 03:28:30',
            ],
        ]);
    }
}
