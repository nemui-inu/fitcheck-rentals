<?php

namespace Database\Factories;

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Item;
use App\Models\OwnerProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_profile_id' => OwnerProfile::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'series' => fake()->words(2, true),
            'character' => fake()->firstName(),
            'size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'description' => fake()->sentence(),
            'daily_rate' => fake()->numberBetween(2, 20) * 5000,
            'deposit' => fake()->numberBetween(5, 30) * 10000,
            'status' => ItemStatus::Active,
        ];
    }

    public function status(ItemStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
