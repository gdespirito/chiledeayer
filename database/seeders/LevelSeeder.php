<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['name' => 'Novato', 'min_points' => 0],
            ['name' => 'Colaborador', 'min_points' => 50],
            ['name' => 'Historiador', 'min_points' => 200],
            ['name' => 'Archivista', 'min_points' => 500],
            ['name' => 'Curador', 'min_points' => 1000],
            ['name' => 'Maestro', 'min_points' => 2500],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(
                ['min_points' => $level['min_points']],
                $level,
            );
        }
    }
}
