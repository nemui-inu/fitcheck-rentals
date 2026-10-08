<?php

use App\Enums\ItemStatus;
use App\Models\Item;
use App\Models\OwnerProfile;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('non admins cannot moderate', function (string $method, string $route) {
    $user = User::factory()->create();
    $item = Item::factory()->create();

    actingAs($user)
        ->{$method}(route($route, str_starts_with($route, 'admin.users') ? $user : $item))
        ->assertForbidden();
})->with([
    ['get', 'admin.users.index'],
    ['patch', 'admin.users.suspend'],
    ['get', 'admin.items.index'],
    ['patch', 'admin.items.take-down'],
    ['patch', 'admin.items.approve'],
]);

test('admins list users and items', function () {
    Item::factory()->status(ItemStatus::PendingReview)->create();

    actingAs($this->admin)->get(route('admin.users.index'))->assertOk();
    actingAs($this->admin)->get(route('admin.items.index'))->assertOk();
});

test('admins suspend and unsuspend users', function () {
    $user = User::factory()->create();

    actingAs($this->admin)->patch(route('admin.users.suspend', $user));
    expect($user->fresh()->isSuspended())->toBeTrue();

    actingAs($this->admin)->patch(route('admin.users.unsuspend', $user));
    expect($user->fresh()->isSuspended())->toBeFalse();
});

test('admins cannot be suspended', function () {
    $other = User::factory()->admin()->create();

    actingAs($this->admin)->patch(route('admin.users.suspend', $other));

    expect($other->fresh()->isSuspended())->toBeFalse();
});

test('admins take down active items with a reason', function () {
    $item = Item::factory()->create();

    actingAs($this->admin)
        ->patch(route('admin.items.take-down', $item), ['reason' => 'Photos show a real weapon.'])
        ->assertSessionHasNoErrors();

    expect($item->fresh())
        ->status->toBe(ItemStatus::TakenDown)
        ->takedown_reason->toBe('Photos show a real weapon.')
        ->taken_down_by->toBe($this->admin->id);
});

test('a reason is required', function () {
    actingAs($this->admin)
        ->patch(route('admin.items.take-down', Item::factory()->create()), ['reason' => ''])
        ->assertSessionHasErrors('reason');
});

test('drafts cannot be taken down', function () {
    $item = Item::factory()->status(ItemStatus::Draft)->create();

    actingAs($this->admin)
        ->patch(route('admin.items.take-down', $item), ['reason' => 'No.'])
        ->assertSessionHasErrors('reason');
});

test('owners resubmit, then admins approve or take down again', function () {
    $profile = OwnerProfile::factory()->create();
    $item = Item::factory()->for($profile)->status(ItemStatus::TakenDown)->create(['takedown_reason' => 'Blurry photos.']);

    actingAs($profile->user)
        ->patch(route('owner.items.resubmit', $item->id))
        ->assertSessionHasNoErrors();
    expect($item->fresh()->status)->toBe(ItemStatus::PendingReview);

    actingAs($this->admin)->patch(route('admin.items.take-down', $item), ['reason' => 'Still blurry.']);
    expect($item->fresh()->status)->toBe(ItemStatus::TakenDown);

    actingAs($profile->user)->patch(route('owner.items.resubmit', $item->id));
    actingAs($this->admin)->patch(route('admin.items.approve', $item))->assertSessionHasNoErrors();

    expect($item->fresh())
        ->status->toBe(ItemStatus::Active)
        ->takedown_reason->toBeNull();
});

test('only taken down items can be resubmitted', function () {
    $profile = OwnerProfile::factory()->create();
    $item = Item::factory()->for($profile)->create();

    actingAs($profile->user)
        ->patch(route('owner.items.resubmit', $item->id))
        ->assertSessionHasErrors('status');
});

test('only pending review items can be approved', function () {
    actingAs($this->admin)
        ->patch(route('admin.items.approve', Item::factory()->status(ItemStatus::TakenDown)->create()))
        ->assertSessionHasErrors('reason');
});
