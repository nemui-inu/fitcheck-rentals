<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ItemUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = today()->addDays(fake()->numberBetween(3, 30));

        return [
            'item_unit_id' => ItemUnit::factory(),
            'renter_id' => User::factory()->withPhone(),
            'start_date' => $start,
            'end_date' => $start->addDays(2),
            'total' => 90000,
            'deposit' => 100000,
            'status' => BookingStatus::Pending,
        ];
    }

    public function status(BookingStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }

    public function between(string $start, string $end): static
    {
        return $this->state(fn (array $attributes) => ['start_date' => $start, 'end_date' => $end]);
    }
}
