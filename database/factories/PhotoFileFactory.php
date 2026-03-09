<?php

namespace Database\Factories;

use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhotoFile>
 */
class PhotoFileFactory extends Factory
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
            'variant' => 'original',
            'path' => 'photos/1/'.fake()->uuid().'.jpg',
            'disk' => 's3',
            'width' => fake()->numberBetween(800, 4000),
            'height' => fake()->numberBetween(600, 3000),
            'size' => fake()->numberBetween(100000, 5000000),
        ];
    }

    /**
     * Indicate that the file is a thumbnail variant.
     */
    public function thumb(): static
    {
        return $this->state(fn (array $attributes) => [
            'variant' => 'thumb',
            'width' => 400,
            'height' => 300,
        ]);
    }

    /**
     * Indicate that the file is a medium variant.
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'variant' => 'medium',
            'width' => 1200,
            'height' => 900,
        ]);
    }
}
