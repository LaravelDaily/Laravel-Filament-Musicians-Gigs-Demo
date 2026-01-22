<?php

namespace Database\Factories;

use App\Enums\GigStatus;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gig>
 */
class GigFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('+1 week', '+3 months');
        $callTime = fake()->time('H:i');
        $performanceTime = date('H:i', strtotime($callTime) + 3600);
        $endTime = date('H:i', strtotime($performanceTime) + 10800);

        return [
            'name' => fake()->company().' '.fake()->randomElement(['Wedding', 'Corporate Event', 'Birthday', 'Anniversary', 'Gala']),
            'date' => $date,
            'call_time' => $callTime,
            'performance_time' => $performanceTime,
            'end_time' => $endTime,
            'venue_name' => fake()->company(),
            'venue_address' => fake()->address(),
            'client_contact_name' => fake()->name(),
            'client_contact_phone' => fake()->phoneNumber(),
            'client_contact_email' => fake()->email(),
            'dress_code' => fake()->randomElement(['Black tie', 'Business casual', 'All black', 'Smart casual', null]),
            'notes' => fake()->optional()->sentence(),
            'pay_info' => fake()->optional()->randomElement(['$200/musician', '$150/hour', 'TBD', '$500 flat']),
            'region_id' => null,
            'status' => GigStatus::Draft,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GigStatus::Draft,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GigStatus::Active,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GigStatus::Cancelled,
        ]);
    }

    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    public function future(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }

    public function withRegion(): static
    {
        return $this->state(fn (array $attributes) => [
            'region_id' => Region::factory(),
        ]);
    }
}
