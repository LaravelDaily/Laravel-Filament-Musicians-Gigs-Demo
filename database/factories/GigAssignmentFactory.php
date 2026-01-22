<?php

namespace Database\Factories;

use App\Enums\AssignmentStatus;
use App\Models\Gig;
use App\Models\Instrument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GigAssignment>
 */
class GigAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gig_id' => Gig::factory(),
            'user_id' => User::factory(),
            'instrument_id' => Instrument::factory(),
            'status' => AssignmentStatus::Pending,
            'pay_amount' => fake()->optional()->randomFloat(2, 100, 500),
            'notes' => fake()->optional()->sentence(),
            'responded_at' => null,
            'subout_reason' => null,
            'decline_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::Pending,
            'responded_at' => null,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::Declined,
            'responded_at' => now(),
            'decline_reason' => fake()->sentence(),
        ]);
    }

    public function suboutRequested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AssignmentStatus::SuboutRequested,
            'responded_at' => now(),
            'subout_reason' => fake()->sentence(),
        ]);
    }
}
