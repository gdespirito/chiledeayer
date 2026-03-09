<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'photo_id' => Photo::factory(),
            'reason' => fake()->sentence(),
            'duplicate_of_id' => null,
            'status' => 'pending',
        ];
    }

    /**
     * Indicate that the report is for a duplicate photo.
     */
    public function duplicate(): static
    {
        return $this->state(fn (array $attributes) => [
            'duplicate_of_id' => Photo::factory(),
        ]);
    }

    /**
     * Indicate that the report has been resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
        ]);
    }

    /**
     * Indicate that the report has been dismissed.
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
        ]);
    }
}
