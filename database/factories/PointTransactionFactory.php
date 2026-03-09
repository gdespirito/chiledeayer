<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\PointAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PointTransaction>
 */
class PointTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pointAction = PointAction::factory();

        return [
            'user_id' => User::factory(),
            'point_action_id' => $pointAction,
            'points' => fake()->numberBetween(1, 20),
            'actionable_type' => Photo::class,
            'actionable_id' => Photo::factory(),
        ];
    }
}
