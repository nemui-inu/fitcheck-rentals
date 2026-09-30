<?php

namespace Database\Factories;

use App\Enums\UnitCondition;
use App\Enums\UnitStatus;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemUnit>
 */
class ItemUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'label' => 'UNIT-'.fake()->unique()->numerify('###'),
            'condition' => fake()->randomElement(UnitCondition::cases()),
            'status' => UnitStatus::Active,
        ];
    }

    public function status(UnitStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
