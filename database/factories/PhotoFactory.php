<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $yearFrom = fake()->numberBetween(1850, 1990);

        return [
            'title' => fake()->sentence(),
            'year_from' => $yearFrom,
            'year_to' => $yearFrom + fake()->numberBetween(0, 10),
            'date_precision' => fake()->randomElement(['exact', 'year', 'decade', 'circa']),
            'heading' => null,
            'pitch' => null,
            'place_id' => Place::factory(),
            'user_id' => User::factory(),
            'source_credit' => fake()->optional()->company(),
            'phash' => null,
        ];
    }

    /**
     * Indicate that the photo has no associated place.
     */
    public function withoutPlace(): static
    {
        return $this->state(fn (array $attributes) => [
            'place_id' => null,
        ]);
    }
}
