<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            ['key' => 'first_upload', 'name' => 'Primera Foto', 'description' => 'Subiste tu primera foto', 'points_awarded' => 5],
            ['key' => 'ten_uploads', 'name' => 'Fotógrafo Activo', 'description' => 'Subiste 10 fotos', 'points_awarded' => 20],
            ['key' => 'fifty_uploads', 'name' => 'Gran Contribuidor', 'description' => 'Subiste 50 fotos', 'points_awarded' => 50],
            ['key' => 'first_comment', 'name' => 'Comentarista', 'description' => 'Escribiste tu primer comentario', 'points_awarded' => 5],
            ['key' => 'first_edit', 'name' => 'Editor', 'description' => 'Hiciste tu primera edición', 'points_awarded' => 5],
            ['key' => 'first_person_tag', 'name' => 'Identificador', 'description' => 'Etiquetaste tu primera persona', 'points_awarded' => 5],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(
                ['key' => $badge['key']],
                $badge,
            );
        }
    }
}
