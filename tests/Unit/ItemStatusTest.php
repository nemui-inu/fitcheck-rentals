<?php

use App\Enums\ItemStatus;

test('item status transitions', function (ItemStatus $from, ItemStatus $to, bool $allowed) {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with([
    [ItemStatus::Draft, ItemStatus::Active, true],
    [ItemStatus::Active, ItemStatus::Paused, true],
    [ItemStatus::Paused, ItemStatus::Active, true],
    [ItemStatus::Active, ItemStatus::TakenDown, true],
    [ItemStatus::TakenDown, ItemStatus::PendingReview, true],
    [ItemStatus::PendingReview, ItemStatus::Active, true],
    [ItemStatus::PendingReview, ItemStatus::TakenDown, true],
    [ItemStatus::Draft, ItemStatus::TakenDown, false],
    [ItemStatus::TakenDown, ItemStatus::Active, false],
    [ItemStatus::Paused, ItemStatus::Draft, false],
]);

test('owners cannot approve their own review', function () {
    expect(ItemStatus::PendingReview->ownerCanTransitionTo(ItemStatus::Active))->toBeFalse()
        ->and(ItemStatus::Paused->ownerCanTransitionTo(ItemStatus::Active))->toBeTrue();
});
