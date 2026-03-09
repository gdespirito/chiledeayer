<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhotoVisit>
 */
class PhotoVisitFactory extends Factory
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
            'user_id' => fake()->boolean(60) ? User::factory() : null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referer' => fake()->optional()->url(),
            'timezone' => fake()->timezone(),
            'country' => fake()->countryCode(),
            'is_bot' => false,
            'visited_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * Indicate the visit is from a bot.
     */
    public function bot(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_bot' => true,
            'user_id' => null,
        ]);
    }
}
