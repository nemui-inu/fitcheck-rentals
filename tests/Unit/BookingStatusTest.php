<?php

use App\Enums\BookingStatus;

test('booking transitions respect who makes them', function (BookingStatus $from, BookingStatus $to, string $actor, bool $allowed) {
    expect($from->canTransitionTo($to, $actor))->toBe($allowed);
})->with([
    [BookingStatus::Pending, BookingStatus::Approved, 'owner', true],
    [BookingStatus::Pending, BookingStatus::Rejected, 'owner', true],
    [BookingStatus::Pending, BookingStatus::Cancelled, 'renter', true],
    [BookingStatus::Approved, BookingStatus::Active, 'owner', true],
    [BookingStatus::Approved, BookingStatus::Cancelled, 'renter', true],
    [BookingStatus::Approved, BookingStatus::Cancelled, 'owner', true],
    [BookingStatus::Active, BookingStatus::Returned, 'owner', true],
    [BookingStatus::Pending, BookingStatus::Approved, 'renter', false],
    [BookingStatus::Pending, BookingStatus::Cancelled, 'owner', false],
    [BookingStatus::Pending, BookingStatus::Active, 'owner', false],
    [BookingStatus::Active, BookingStatus::Cancelled, 'renter', false],
    [BookingStatus::Returned, BookingStatus::Active, 'owner', false],
    [BookingStatus::Rejected, BookingStatus::Approved, 'owner', false],
]);
