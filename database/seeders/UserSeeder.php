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
        // Create Admin user
        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        // Get existing roles
        $roleAdmin = Role::findByName('Admin');
        $roleKaryawan = Role::findByName('Karyawan');

        // Assign roles
        $user->assignRole($roleAdmin);

        // Create 100 fake users
        // User::factory()
        //     ->count(100)
        //     ->create()
        //     ->each(function ($user) use ($roleKaryawan) {
        //         $user->assignRole($roleKaryawan);
        //     });
    }
}
