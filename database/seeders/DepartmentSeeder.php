<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Produksi', 'description' => 'Departemen Produksi'],
            ['name' => 'Gudang', 'description' => 'Departemen Gudang'],
            ['name' => 'HRD', 'description' => 'Departemen Sumber Daya Manusia'],
            ['name' => 'Maintenance', 'description' => 'Departemen Pemeliharaan'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
