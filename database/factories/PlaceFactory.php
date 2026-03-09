<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Place>
 */
class PlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(['precise', 'approximate']),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'google_place_id' => null,
            'bounding_box' => null,
            'country' => 'Chile',
            'region' => fake()->state(),
            'city' => $name,
        ];
    }
}
