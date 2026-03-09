<?php

namespace Database\Seeders;

use App\Models\PointAction;
use Illuminate\Database\Seeder;

class PointActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            ['key' => 'photo_uploaded', 'label' => 'Foto subida', 'points' => 10],
            ['key' => 'metadata_edited', 'label' => 'Metadata editada', 'points' => 5],
            ['key' => 'comment_created', 'label' => 'Comentario creado', 'points' => 3],
            ['key' => 'photo_voted', 'label' => 'Voto en foto', 'points' => 1],
            ['key' => 'person_tagged', 'label' => 'Persona etiquetada', 'points' => 5],
            ['key' => 'comparison_uploaded', 'label' => 'Foto comparativa subida', 'points' => 15],
        ];

        foreach ($actions as $action) {
            PointAction::updateOrCreate(
                ['key' => $action['key']],
                $action,
            );
        }
    }
}
