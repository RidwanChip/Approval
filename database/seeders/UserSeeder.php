<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $testUser = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        // Buat role Admin dan Penulis
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleKaryawan = Role::create(['name' => 'Karyawan']);

        // Assign role ke user
        $user->assignRole($roleAdmin);
        $testUser->assignRole($roleKaryawan);
    }
}
