<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            PositionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
