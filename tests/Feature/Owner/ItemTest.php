<?php

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\OwnerProfile;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->profile = OwnerProfile::factory()->create();
    $this->owner = $this->profile->user;
    $this->category = Category::factory()->create(['code' => 'WIG']);
});

function itemPayload(array $overrides = []): array
{
    return [
        'category_id' => test()->category->id,
        'name' => 'Frieren wig',
        'series' => 'Frieren',
        'character' => 'Frieren',
        'size' => 'Free',
        'description' => 'Silver twin tails.',
        'daily_rate' => '350.50',
        'deposit' => '1000',
        ...$overrides,
    ];
}

test('owners can list their items', function () {
    Item::factory()->for($this->profile)->count(2)->create();

    actingAs($this->owner)->get(route('owner.items.index'))->assertOk();
});

test('owners create items as drafts with money in centavos', function () {
    actingAs($this->owner)
        ->post(route('owner.items.store'), itemPayload())
        ->assertRedirect();

    $item = $this->profile->items()->sole();

    expect($item->daily_rate)->toBe(35050)
        ->and($item->deposit)->toBe(100000)
        ->and($item->status)->toBe(ItemStatus::Draft);
});

test('status and owner cannot be mass assigned', function () {
    $other = OwnerProfile::factory()->create();

    actingAs($this->owner)
        ->post(route('owner.items.store'), itemPayload(['status' => 'active', 'owner_profile_id' => $other->id]))
        ->assertRedirect();

    $item = Item::sole();

    expect($item->status)->toBe(ItemStatus::Draft)
        ->and($item->owner_profile_id)->toBe($this->profile->id);
});

test('inactive categories cannot be picked for new items', function () {
    $inactive = Category::factory()->inactive()->create();

    actingAs($this->owner)
        ->post(route('owner.items.store'), itemPayload(['category_id' => $inactive->id]))
        ->assertSessionHasErrors('category_id');
});

test('existing items keep their inactive category on update', function () {
    $item = Item::factory()->for($this->profile)->for($this->category)->create();
    $this->category->update(['is_active' => false]);

    actingAs($this->owner)
        ->put(route('owner.items.update', $item->id), itemPayload(['name' => 'Renamed']))
        ->assertSessionHasNoErrors();

    expect($item->fresh()->name)->toBe('Renamed');
});

test('owners cannot touch items of another owner', function (string $method, string $route) {
    $foreign = Item::factory()->create();

    actingAs($this->owner)
        ->{$method}(route($route, $foreign->id), itemPayload())
        ->assertNotFound();
})->with([
    ['get', 'owner.items.edit'],
    ['put', 'owner.items.update'],
    ['delete', 'owner.items.destroy'],
    ['patch', 'owner.items.status'],
]);

test('owners can publish an item with units and pause it', function () {
    $item = Item::factory()->for($this->profile)->status(ItemStatus::Draft)->create();
    ItemUnit::factory()->for($item)->create();

    actingAs($this->owner)
        ->patch(route('owner.items.status', $item->id), ['status' => 'active'])
        ->assertSessionHasNoErrors();
    expect($item->fresh()->status)->toBe(ItemStatus::Active);

    actingAs($this->owner)
        ->patch(route('owner.items.status', $item->id), ['status' => 'paused'])
        ->assertSessionHasNoErrors();
    expect($item->fresh()->status)->toBe(ItemStatus::Paused);
});

test('items without units cannot be published', function () {
    $item = Item::factory()->for($this->profile)->status(ItemStatus::Draft)->create();

    actingAs($this->owner)
        ->patch(route('owner.items.status', $item->id), ['status' => 'active'])
        ->assertSessionHasErrors('status');
});

test('owners cannot set admin only or invalid statuses', function (ItemStatus $from, string $to) {
    $item = Item::factory()->for($this->profile)->status($from)->create();
    ItemUnit::factory()->for($item)->create();

    actingAs($this->owner)
        ->patch(route('owner.items.status', $item->id), ['status' => $to])
        ->assertSessionHasErrors('status');

    expect($item->fresh()->status)->toBe($from);
})->with([
    'draft to paused' => [ItemStatus::Draft, 'paused'],
    'taken down to active' => [ItemStatus::TakenDown, 'active'],
    'active to taken down' => [ItemStatus::Active, 'taken_down'],
    'pending review to active' => [ItemStatus::PendingReview, 'active'],
]);

test('owners can delete an item with its units', function () {
    $item = Item::factory()->for($this->profile)->create();
    ItemUnit::factory()->for($item)->create();

    actingAs($this->owner)
        ->delete(route('owner.items.destroy', $item->id))
        ->assertRedirect(route('owner.items.index'));

    expect(Item::count())->toBe(0)->and(ItemUnit::count())->toBe(0);
});
