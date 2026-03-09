<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revision>
 */
class RevisionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'revisionable_type' => Photo::class,
            'revisionable_id' => Photo::factory(),
            'user_id' => User::factory(),
            'old_values' => ['description' => fake()->paragraph()],
            'new_values' => ['description' => fake()->paragraph()],
        ];
    }
}
