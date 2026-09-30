<?php

use App\Models\Category;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('non admins cannot manage categories', function () {
    actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertForbidden();
});

test('admins can list categories', function () {
    Category::factory()->count(2)->create();

    actingAs($this->admin)->get(route('admin.categories.index'))->assertOk();
});

test('admins can create a category with an uppercased code', function () {
    actingAs($this->admin)
        ->post(route('admin.categories.store'), ['name' => 'Wig', 'code' => 'wig'])
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::firstWhere('name', 'Wig')->code)->toBe('WIG');
});

test('category code must be 2 to 6 letters', function (string $code) {
    actingAs($this->admin)
        ->post(route('admin.categories.store'), ['name' => 'Wig', 'code' => $code])
        ->assertSessionHasErrors('code');
})->with(['W', 'TOOLONGX', 'W1G', '']);

test('name and code are unique', function () {
    Category::factory()->create(['name' => 'Wig', 'code' => 'WIG']);

    actingAs($this->admin)
        ->post(route('admin.categories.store'), ['name' => 'Wig', 'code' => 'WIG'])
        ->assertSessionHasErrors(['name', 'code']);
});

test('admins can update and deactivate a category', function () {
    $category = Category::factory()->create(['name' => 'Wig', 'code' => 'WIG']);

    actingAs($this->admin)
        ->put(route('admin.categories.update', $category), ['name' => 'Wigs', 'code' => 'WIG', 'is_active' => false])
        ->assertRedirect(route('admin.categories.index'));

    $category->refresh();

    expect($category->name)->toBe('Wigs')
        ->and($category->is_active)->toBeFalse();
});

test('admins can delete an unused category', function () {
    $category = Category::factory()->create();

    actingAs($this->admin)
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::count())->toBe(0);
});
