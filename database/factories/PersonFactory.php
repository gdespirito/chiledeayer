<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'type' => 'unknown',
            'slug' => null,
            'bio' => null,
        ];
    }

    /**
     * Indicate that the person is a public figure.
     */
    public function public(): static
    {
        return $this->state(function (array $attributes) {
            $name = $attributes['name'] ?? fake()->name();

            return [
                'type' => 'public',
                'slug' => Str::slug($name),
                'bio' => fake()->paragraph(),
            ];
        });
    }
}
