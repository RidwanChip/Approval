<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Operator', 'description' => 'Operator Produksi'],
            ['name' => 'HR Manager', 'description' => 'HR Manager'],
            ['name' => 'Staff', 'description' => 'Staff'],
            ['name' => 'Maintenance Produksi', 'description' => 'Pemeliharaan'],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
