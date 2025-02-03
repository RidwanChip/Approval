<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $adminUser = User::create([
            'name' => 'Admin Pabrik',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $admin = Employee::create([
            'user_id' => $adminUser->id,
            'department_id' => Department::where('name', 'HRD')->first()->id,
            'contact' => '081234567890'
        ]);

        $adminUser->assignRole('Admin');

        // Create Sample Employees
        $employees = [
            [
                'user' => [
                    'name' => 'Budi Santoso',
                    'email' => 'budi@example.com',
                    'password' => Hash::make('password'),
                ],
                'department_id' => Department::where('name', 'Produksi')->first()->id,
                'contact' => '081234567891',
                'role' => 'Supervisor'
            ],
            [
                'user' => [
                    'name' => 'Ani Wijaya',
                    'email' => 'ani@example.com',
                    'password' => Hash::make('password'),
                ],
                'department_id' => Department::where('name', 'Produksi')->first()->id,
                'contact' => '081234567892',
                'role' => 'Operator Mesin'
            ]
        ];

        foreach ($employees as $employeeData) {
            $user = User::create($employeeData['user']);
            $employee = Employee::create(array_merge(
                collect($employeeData)->except(['user', 'role'])->toArray(),
                ['user_id' => $user->id]
            ));
            $user->assignRole($employeeData['role']);
        }

        // Generate random employees
        Employee::factory()->count(5)->create();
    }
}
