<?php

use App\Enums\ItemStatus;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\User;

use function Pest\Laravel\getJson;

test('only active items are listed', function (ItemStatus $status) {
    Item::factory()->create(['name' => 'Listed']);
    Item::factory()->status($status)->create();

    getJson(route('api.items.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Listed');
})->with([ItemStatus::Draft, ItemStatus::Paused, ItemStatus::PendingReview, ItemStatus::TakenDown]);

test('items of suspended owners are hidden', function () {
    $item = Item::factory()->create();
    User::whereKey($item->ownerProfile->user_id)->update(['suspended_at' => now()]);

    getJson(route('api.items.index'))->assertOk()->assertJsonCount(0, 'data');
    getJson(route('api.items.show', $item->id))->assertNotFound();
});

test('search matches name, series, and character', function (string $search) {
    Item::factory()->create(['name' => 'Silver wig', 'series' => 'Frieren', 'character' => 'Fern']);
    Item::factory()->create(['name' => 'Red cape', 'series' => 'Other', 'character' => 'Someone']);

    getJson(route('api.items.index', ['search' => $search]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Silver wig');
})->with(['silver', 'frieren', 'fern']);

test('filters by category, size, and price range', function () {
    $wig = Category::factory()->create(['code' => 'WIG']);
    Item::factory()->for($wig)->create(['name' => 'Match', 'size' => 'M', 'daily_rate' => 30000]);
    Item::factory()->for($wig)->create(['size' => 'M', 'daily_rate' => 90000]);
    Item::factory()->create(['size' => 'M', 'daily_rate' => 30000]);

    getJson(route('api.items.index', ['category' => 'wig', 'size' => 'M', 'min_price' => 200, 'max_price' => 500]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Match');
});

test('returns 422 when a filter is invalid', function () {
    getJson(route('api.items.index', ['min_price' => -5]))->assertUnprocessable();
});

test('lists money as integer centavos with pagination meta', function () {
    Item::factory()->create(['daily_rate' => 35000, 'deposit' => 100000]);

    $response = getJson(route('api.items.index'))
        ->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);

    expect($response->json('data.0.daily_rate'))->toBeInt()->toBe(35000)
        ->and($response->json('data.0.deposit'))->toBeInt()->toBe(100000);
});

test('item detail returns the category, images, owner, and free unit count', function () {
    $item = Item::factory()->create();
    ItemUnit::factory()->for($item)->create();

    getJson(route('api.items.show', $item->id))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'daily_rate',
                'deposit',
                'category' => ['id', 'name', 'code'],
                'cover_image',
                'images',
                'owner' => ['shop_name', 'meetup_area'],
                'available_units_count',
            ],
        ])
        ->assertJsonPath('data.available_units_count', 1)
        ->assertJsonPath('data.owner.shop_name', $item->ownerProfile->shop_name);
});

test('returns 404 for an item that is not listed', function (ItemStatus $status) {
    $item = Item::factory()->status($status)->create();

    getJson(route('api.items.show', $item->id))->assertNotFound();
})->with([ItemStatus::Draft, ItemStatus::Paused, ItemStatus::PendingReview, ItemStatus::TakenDown]);
