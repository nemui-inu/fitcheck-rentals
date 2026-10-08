<?php

use App\Enums\UnitStatus;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\OwnerProfile;
use App\Support\UnitLabel;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->profile = OwnerProfile::factory()->create();
    $this->category = Category::factory()->create(['code' => 'WIG']);
    $this->item = Item::factory()->for($this->profile)->for($this->category)->create();
});

test('suggested labels continue the owner numbering per category', function () {
    expect(UnitLabel::suggest($this->profile, $this->category))->toBe('WIG-001');

    ItemUnit::factory()->for($this->item)->create(['label' => 'WIG-002']);
    ItemUnit::factory()->for(Item::factory()->create(['category_id' => $this->category->id]))->create(['label' => 'WIG-009']);

    expect(UnitLabel::suggest($this->profile, $this->category))->toBe('WIG-003');
});

test('owners add units', function () {
    actingAs($this->profile->user)
        ->post(route('owner.items.units.store', $this->item->id), ['label' => 'wig-001', 'condition' => 'good'])
        ->assertSessionHasNoErrors();

    expect($this->item->units()->sole()->label)->toBe('WIG-001');
});

test('labels are unique per owner but not across owners', function () {
    ItemUnit::factory()->for($this->item)->create(['label' => 'WIG-001']);
    ItemUnit::factory()->create(['label' => 'WIG-002']);

    $other = Item::factory()->for($this->profile)->create();

    actingAs($this->profile->user)
        ->post(route('owner.items.units.store', $other->id), ['label' => 'WIG-001', 'condition' => 'good'])
        ->assertSessionHasErrors('label');

    actingAs($this->profile->user)
        ->post(route('owner.items.units.store', $other->id), ['label' => 'WIG-002', 'condition' => 'good'])
        ->assertSessionHasNoErrors();
});

test('owners retire units', function () {
    $unit = ItemUnit::factory()->for($this->item)->create(['label' => 'WIG-001']);

    actingAs($this->profile->user)
        ->put(route('owner.items.units.update', [$this->item->id, $unit->id]), [
            'label' => 'WIG-001',
            'condition' => 'worn',
            'status' => 'retired',
        ])
        ->assertSessionHasNoErrors();

    expect($unit->fresh()->status)->toBe(UnitStatus::Retired);
});

test('owners cannot add units to another owner item', function () {
    actingAs($this->profile->user)
        ->post(route('owner.items.units.store', Item::factory()->create()->id), ['label' => 'X-1', 'condition' => 'good'])
        ->assertNotFound();
});
