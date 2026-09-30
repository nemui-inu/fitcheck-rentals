<?php

use App\Enums\BookingStatus;
use App\Enums\ItemStatus;
use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\travelTo;

beforeEach(function () {
    travelTo(now()->setDate(2026, 10, 1)->setTime(10, 0));

    $this->item = Item::factory()->create(['daily_rate' => 35000, 'deposit' => 100000]);
    $this->unitA = ItemUnit::factory()->for($this->item)->create();
    $this->unitB = ItemUnit::factory()->for($this->item)->create();
    $this->renter = User::factory()->withPhone()->create();
    $this->owner = $this->item->ownerProfile->user;
});

function requestDates(string $start = '2026-10-12', string $end = '2026-10-14'): array
{
    return ['start_date' => $start, 'end_date' => $end];
}

test('renters request a booking on the first free unit with an inclusive total', function () {
    actingAs($this->renter)
        ->post(route('bookings.store', $this->item->id), requestDates())
        ->assertSessionHasNoErrors();

    $booking = Booking::sole();

    expect($booking->item_unit_id)->toBe($this->unitA->id)
        ->and($booking->total)->toBe(3 * 35000)
        ->and($booking->deposit)->toBe(100000)
        ->and($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->renter_id)->toBe($this->renter->id);
});

test('busy and inactive units are skipped', function () {
    Booking::factory()->for($this->unitA, 'unit')->status(BookingStatus::Approved)->between('2026-10-13', '2026-10-20')->create();

    actingAs($this->renter)->post(route('bookings.store', $this->item->id), requestDates());

    expect(Booking::where('renter_id', $this->renter->id)->sole()->item_unit_id)->toBe($this->unitB->id);
});

test('pending bookings never block', function () {
    Booking::factory()->for($this->unitA, 'unit')->between('2026-10-12', '2026-10-14')->create();

    actingAs($this->renter)->post(route('bookings.store', $this->item->id), requestDates());

    expect(Booking::where('renter_id', $this->renter->id)->sole()->item_unit_id)->toBe($this->unitA->id);
});

test('fully booked ranges are refused', function () {
    Booking::factory()->for($this->unitA, 'unit')->status(BookingStatus::Active)->between('2026-10-10', '2026-10-12')->create();
    $this->unitB->forceFill(['status' => UnitStatus::Maintenance])->save();

    actingAs($this->renter)
        ->post(route('bookings.store', $this->item->id), requestDates())
        ->assertSessionHasErrors(['start_date' => 'Those dates are fully booked. Pick another range.']);
});

test('one pending request per renter per item', function () {
    actingAs($this->renter)->post(route('bookings.store', $this->item->id), requestDates());
    actingAs($this->renter)
        ->post(route('bookings.store', $this->item->id), requestDates('2026-11-01', '2026-11-02'))
        ->assertSessionHasErrors('start_date');
});

test('owners cannot book their own items', function () {
    $this->owner->forceFill(['phone' => '09170000000'])->save();

    actingAs($this->owner)
        ->post(route('bookings.store', $this->item->id), requestDates())
        ->assertSessionHasErrors('start_date');
});

test('renters without a phone must give one, which is saved', function () {
    $renter = User::factory()->create(['phone' => null]);

    actingAs($renter)
        ->post(route('bookings.store', $this->item->id), requestDates())
        ->assertSessionHasErrors('phone');

    actingAs($renter)
        ->post(route('bookings.store', $this->item->id), [...requestDates(), 'phone' => '09171112222'])
        ->assertSessionHasNoErrors();

    expect($renter->fresh()->phone)->toBe('09171112222');
});

test('dates must not be in the past or reversed', function (string $start, string $end) {
    actingAs($this->renter)
        ->post(route('bookings.store', $this->item->id), requestDates($start, $end))
        ->assertSessionHasErrors();
})->with([['2026-09-30', '2026-10-02'], ['2026-10-14', '2026-10-12']]);

test('inactive items cannot be booked', function () {
    $this->item->forceFill(['status' => ItemStatus::Paused])->save();

    actingAs($this->renter)
        ->post(route('bookings.store', $this->item->id), requestDates())
        ->assertNotFound();
});

test('approval keeps the unit when it is still free', function () {
    $booking = Booking::factory()->for($this->unitA, 'unit')->between('2026-10-12', '2026-10-14')->create();

    actingAs($this->owner)
        ->patch(route('owner.bookings.update', $booking->reference), ['status' => 'approved'])
        ->assertSessionHasNoErrors();

    expect($booking->fresh())
        ->status->toBe(BookingStatus::Approved)
        ->item_unit_id->toBe($this->unitA->id);
});

test('approval swaps to another free unit when the assigned one got taken', function () {
    $booking = Booking::factory()->for($this->unitA, 'unit')->between('2026-10-12', '2026-10-14')->create();
    Booking::factory()->for($this->unitA, 'unit')->status(BookingStatus::Approved)->between('2026-10-14', '2026-10-15')->create();

    actingAs($this->owner)->patch(route('owner.bookings.update', $booking->reference), ['status' => 'approved']);

    expect($booking->fresh())
        ->status->toBe(BookingStatus::Approved)
        ->item_unit_id->toBe($this->unitB->id);
});

test('approval fails and stays pending when no unit is free', function () {
    $booking = Booking::factory()->for($this->unitA, 'unit')->between('2026-10-12', '2026-10-14')->create();
    Booking::factory()->for($this->unitA, 'unit')->status(BookingStatus::Approved)->between('2026-10-12', '2026-10-12')->create();
    Booking::factory()->for($this->unitB, 'unit')->status(BookingStatus::Active)->between('2026-10-14', '2026-10-16')->create();

    actingAs($this->owner)
        ->patch(route('owner.bookings.update', $booking->reference), ['status' => 'approved'])
        ->assertSessionHasErrors('status');

    expect($booking->fresh()->status)->toBe(BookingStatus::Pending);
});

test('owners walk a booking through pickup and return', function () {
    $booking = Booking::factory()->for($this->unitA, 'unit')->status(BookingStatus::Approved)->create();

    actingAs($this->owner)->patch(route('owner.bookings.update', $booking->reference), ['status' => 'active'])->assertSessionHasNoErrors();
    actingAs($this->owner)->patch(route('owner.bookings.update', $booking->reference), ['status' => 'returned'])->assertSessionHasNoErrors();

    expect($booking->fresh())
        ->status->toBe(BookingStatus::Returned)
        ->returned_at->not->toBeNull();
});

test('invalid owner transitions are refused', function (BookingStatus $from, string $to) {
    $booking = Booking::factory()->for($this->unitA, 'unit')->status($from)->create();

    actingAs($this->owner)
        ->patch(route('owner.bookings.update', $booking->reference), ['status' => $to])
        ->assertSessionHasErrors('status');

    expect($booking->fresh()->status)->toBe($from);
})->with([
    [BookingStatus::Pending, 'active'],
    [BookingStatus::Pending, 'cancelled'],
    [BookingStatus::Returned, 'active'],
    [BookingStatus::Rejected, 'approved'],
    [BookingStatus::Approved, 'pending'],
]);

test('owners cannot touch bookings on other owners items', function () {
    $booking = Booking::factory()->create();

    actingAs($this->owner)
        ->patch(route('owner.bookings.update', $booking->reference), ['status' => 'rejected'])
        ->assertNotFound();
});

test('renters cancel their pending and approved bookings only', function () {
    $pending = Booking::factory()->for($this->renter, 'renter')->create();
    $active = Booking::factory()->for($this->renter, 'renter')->status(BookingStatus::Active)->create();

    actingAs($this->renter)->patch(route('bookings.cancel', $pending->reference))->assertSessionHasNoErrors();
    actingAs($this->renter)->patch(route('bookings.cancel', $active->reference))->assertSessionHasErrors('status');

    expect($pending->fresh()->status)->toBe(BookingStatus::Cancelled)
        ->and($active->fresh()->status)->toBe(BookingStatus::Active);
});

test('renters only see their own bookings', function () {
    $mine = Booking::factory()->for($this->renter, 'renter')->create();
    $theirs = Booking::factory()->create();

    actingAs($this->renter)->get(route('bookings.index'))->assertOk();
    actingAs($this->renter)->get(route('bookings.show', $mine->reference))->assertOk();
    actingAs($this->renter)->get(route('bookings.show', $theirs->reference))->assertNotFound();
    actingAs($this->renter)->patch(route('bookings.cancel', $theirs->reference))->assertNotFound();
});

test('booking detail shows days late after a late return', function () {
    $booking = Booking::factory()->for($this->renter, 'renter')->status(BookingStatus::Returned)
        ->between('2026-10-12', '2026-10-14')->create(['returned_at' => '2026-10-16 09:00:00']);

    actingAs($this->renter)
        ->get(route('bookings.show', $booking->reference))
        ->assertInertia(fn ($page) => $page->where('daysLate', 2));
});

test('items and units with bookings cannot be deleted', function () {
    Booking::factory()->for($this->unitA, 'unit')->create();

    actingAs($this->owner)->delete(route('owner.items.units.destroy', [$this->item->id, $this->unitA->id]));
    actingAs($this->owner)->delete(route('owner.items.destroy', $this->item->id));

    expect($this->unitA->fresh())->not->toBeNull()
        ->and($this->item->fresh())->not->toBeNull();
});

test('owner pages list their bookings', function () {
    Booking::factory()->for($this->unitA, 'unit')->create();

    actingAs($this->owner)->get(route('owner.bookings.index'))->assertOk();
    actingAs($this->owner)
        ->get(route('owner.dashboard'))
        ->assertInertia(fn ($page) => $page->has('pendingBookings', 1));
});
