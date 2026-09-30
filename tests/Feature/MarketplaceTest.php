<?php

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('only active items are listed', function (ItemStatus $status) {
    Item::factory()->create(['name' => 'Listed']);
    Item::factory()->status($status)->create();

    get(route('marketplace.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('marketplace/index')
            ->has('items.data', 1)
            ->where('items.data.0.name', 'Listed'));
})->with([ItemStatus::Draft, ItemStatus::Paused, ItemStatus::PendingReview, ItemStatus::TakenDown]);

test('items of suspended owners are hidden', function () {
    $item = Item::factory()->create();
    User::whereKey($item->ownerProfile->user_id)->update(['suspended_at' => now()]);

    get(route('marketplace.index'))->assertInertia(fn (Assert $page) => $page->has('items.data', 0));
    get(route('marketplace.show', $item->id))->assertNotFound();
});

test('search matches name, series, and character', function (string $search) {
    Item::factory()->create(['name' => 'Silver wig', 'series' => 'Frieren', 'character' => 'Fern']);
    Item::factory()->create(['name' => 'Red cape', 'series' => 'Other', 'character' => 'Someone']);

    get(route('marketplace.index', ['search' => $search]))
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.name', 'Silver wig'));
})->with(['silver', 'frieren', 'fern']);

test('filters by category, size, and price range', function () {
    $wig = Category::factory()->create(['code' => 'WIG']);
    Item::factory()->for($wig)->create(['name' => 'Match', 'size' => 'M', 'daily_rate' => 30000]);
    Item::factory()->for($wig)->create(['size' => 'M', 'daily_rate' => 90000]);
    Item::factory()->for($wig)->create(['size' => 'L', 'daily_rate' => 30000]);
    Item::factory()->create(['size' => 'M', 'daily_rate' => 30000]);

    get(route('marketplace.index', ['category' => 'wig', 'size' => 'M', 'min_price' => 200, 'max_price' => 500]))
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.name', 'Match'));
});

test('item detail shows active items only', function () {
    $item = Item::factory()->create();
    $draft = Item::factory()->status(ItemStatus::Draft)->create();

    get(route('marketplace.show', $item->id))->assertOk();
    get(route('marketplace.show', $draft->id))->assertNotFound();
});
