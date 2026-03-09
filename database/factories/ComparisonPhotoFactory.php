<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ComparisonPhoto>
 */
class ComparisonPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_id' => Photo::factory(),
            'user_id' => User::factory(),
            'description' => fake()->optional()->paragraph(),
            'taken_at' => fake()->optional()->date(),
            'original_path' => 'comparisons/1/'.fake()->uuid().'.jpg',
            'medium_path' => null,
            'thumb_path' => null,
        ];
    }

    /**
     * Indicate that all variants have been processed.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'medium_path' => 'comparisons/1/medium/'.fake()->uuid().'.jpg',
            'thumb_path' => 'comparisons/1/thumb/'.fake()->uuid().'.jpg',
        ]);
    }
}
