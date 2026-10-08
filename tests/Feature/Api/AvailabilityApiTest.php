<?php

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Item;
use App\Models\ItemUnit;

use function Pest\Laravel\getJson;
use function Pest\Laravel\travelTo;

beforeEach(function () {
    travelTo(now()->setDate(2026, 10, 1)->setTime(10, 0));

    $this->item = Item::factory()->create();
    $this->unit = ItemUnit::factory()->for($this->item)->create();
});

function availabilityQuery(string $start = '2026-10-12', string $end = '2026-10-14'): array
{
    return ['start' => $start, 'end' => $end];
}

test('a free range is available', function () {
    getJson(route('api.items.availability', array_merge(['item' => $this->item->id], availabilityQuery())))
        ->assertOk()
        ->assertJson([
            'start' => '2026-10-12',
            'end' => '2026-10-14',
            'available' => true,
            'free_units' => 1,
        ]);
});

test('an overlapping approved booking makes the range unavailable', function () {
    Booking::factory()->for($this->unit, 'unit')->status(BookingStatus::Approved)->between('2026-10-13', '2026-10-20')->create();

    getJson(route('api.items.availability', array_merge(['item' => $this->item->id], availabilityQuery())))
        ->assertOk()
        ->assertJson(['available' => false, 'free_units' => 0]);
});

test('pending bookings do not block the range', function () {
    Booking::factory()->for($this->unit, 'unit')->between('2026-10-12', '2026-10-14')->create();

    getJson(route('api.items.availability', array_merge(['item' => $this->item->id], availabilityQuery())))
        ->assertOk()
        ->assertJson(['available' => true, 'free_units' => 1]);
});

test('units that are not active are not counted', function () {
    $this->unit->forceFill(['status' => UnitStatus::Maintenance])->save();

    getJson(route('api.items.availability', array_merge(['item' => $this->item->id], availabilityQuery())))
        ->assertOk()
        ->assertJson(['available' => false, 'free_units' => 0]);
});

test('returns 422 when the start date is in the past', function () {
    getJson(route('api.items.availability', array_merge(['item' => $this->item->id], availabilityQuery('2026-09-01', '2026-09-03'))))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start' => 'Pick a start date from today onward.']);
});

test('returns 422 when the end date is before the start date', function () {
    getJson(route('api.items.availability', array_merge(['item' => $this->item->id], availabilityQuery('2026-10-14', '2026-10-12'))))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['end' => 'Pick an end date on or after the start date.']);
});

test('returns 404 for an item that is not listed', function () {
    $draft = Item::factory()->status(ItemStatus::Draft)->create();

    getJson(route('api.items.availability', array_merge(['item' => $draft->id], availabilityQuery())))
        ->assertNotFound();
});
