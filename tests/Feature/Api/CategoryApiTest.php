<?php

use App\Models\Category;

use function Pest\Laravel\getJson;

test('lists active categories sorted by name', function () {
    Category::factory()->create(['name' => 'Wig', 'code' => 'WIG']);
    Category::factory()->create(['name' => 'Costume', 'code' => 'COS']);
    Category::factory()->inactive()->create(['name' => 'Hidden', 'code' => 'HID']);

    getJson(route('api.categories.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Costume')
        ->assertJsonPath('data.1.name', 'Wig')
        ->assertJsonStructure(['data' => [['id', 'name', 'code']]]);
});
