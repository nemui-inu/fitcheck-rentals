<?php

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\OwnerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
    $this->profile = OwnerProfile::factory()->create();
    $this->item = Item::factory()->for($this->profile)->create();
});

test('owners upload photos under the item folder', function () {
    actingAs($this->profile->user)
        ->post(route('owner.items.images.store', $this->item->id), [
            'photos' => [UploadedFile::fake()->create('a.jpg', 100, 'image/jpeg'), UploadedFile::fake()->create('b.png', 100, 'image/png')],
        ])
        ->assertSessionHasNoErrors();

    $images = $this->item->images()->get();

    expect($images)->toHaveCount(2)
        ->and($images->pluck('sort_order')->all())->toBe([0, 1])
        ->and($images->first()->path)->toStartWith("items/{$this->item->id}/");

    $images->each(fn (ItemImage $image) => Storage::disk('public')->assertExists($image->path));
});

test('non images are rejected', function () {
    actingAs($this->profile->user)
        ->post(route('owner.items.images.store', $this->item->id), [
            'photos' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ])
        ->assertSessionHasErrors('photos.0');
});

test('moving a photo up makes it the cover', function () {
    $first = ItemImage::factory()->for($this->item)->create(['sort_order' => 0]);
    $second = ItemImage::factory()->for($this->item)->create(['sort_order' => 1]);

    actingAs($this->profile->user)
        ->patch(route('owner.items.images.update', [$this->item->id, $second->id]), ['direction' => 'up'])
        ->assertSessionHasNoErrors();

    expect($this->item->coverImage()->first()->id)->toBe($second->id);
});

test('deleting a photo removes its file', function () {
    Storage::disk('public')->put('items/1/photo.jpg', 'x');
    $image = ItemImage::factory()->for($this->item)->create(['path' => 'items/1/photo.jpg']);

    actingAs($this->profile->user)
        ->delete(route('owner.items.images.destroy', [$this->item->id, $image->id]));

    expect(ItemImage::count())->toBe(0);
    Storage::disk('public')->assertMissing('items/1/photo.jpg');
});

test('owners cannot delete photos of another owner', function () {
    $image = ItemImage::factory()->create();

    actingAs($this->profile->user)
        ->delete(route('owner.items.images.destroy', [$image->item_id, $image->id]))
        ->assertNotFound();
});
