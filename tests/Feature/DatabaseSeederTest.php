<?php

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Models\Booking;
use App\Models\Item;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

use function Pest\Laravel\seed;

test('seed data covers every status and demo account', function () {
    seed(DatabaseSeeder::class);

    foreach (['admin', 'owner', 'renter', 'both'] as $name) {
        expect(User::where('email', "{$name}@fitcheck.test")->exists())->toBeTrue();
    }

    expect(User::whereNotNull('suspended_at')->count())->toBe(1)
        ->and(Item::distinct()->pluck('status')->map->value->sort()->values()->all())
        ->toBe(collect(ItemStatus::cases())->map->value->sort()->values()->all())
        ->and(Booking::distinct()->pluck('status')->map->value->sort()->values()->all())
        ->toBe(collect(BookingStatus::cases())->map->value->sort()->values()->all());
});
