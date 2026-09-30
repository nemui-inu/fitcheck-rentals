<?php

use App\Models\OwnerProfile;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests cannot see the setup page', function () {
    get(route('owner.setup'))->assertRedirect(route('login'));
});

test('users can see the setup page', function () {
    actingAs(User::factory()->create())
        ->get(route('owner.setup'))
        ->assertOk();
});

test('owners are sent to their dashboard from the setup page', function () {
    $owner = OwnerProfile::factory()->create()->user;

    actingAs($owner)
        ->get(route('owner.setup'))
        ->assertRedirect(route('owner.dashboard'));
});

test('users become owners with a shop name, meetup area, and phone', function () {
    $user = User::factory()->create(['phone' => null]);

    actingAs($user)
        ->post(route('owner.setup.store'), [
            'shop_name' => 'Wig Closet',
            'meetup_area' => 'Cubao',
            'phone' => '09171234567',
        ])
        ->assertRedirect(route('owner.dashboard'));

    $user->refresh();

    expect($user->isOwner())->toBeTrue()
        ->and($user->phone)->toBe('09171234567')
        ->and($user->ownerProfile->shop_name)->toBe('Wig Closet');
});

test('phone is required when the user has none', function () {
    actingAs(User::factory()->create(['phone' => null]))
        ->post(route('owner.setup.store'), [
            'shop_name' => 'Wig Closet',
            'meetup_area' => 'Cubao',
        ])
        ->assertSessionHasErrors('phone');
});

test('phone can be skipped when the user already has one', function () {
    $user = User::factory()->create(['phone' => '09170000000']);

    actingAs($user)
        ->post(route('owner.setup.store'), [
            'shop_name' => 'Wig Closet',
            'meetup_area' => 'Cubao',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->phone)->toBe('09170000000');
});

test('owners cannot create a second profile', function () {
    $owner = OwnerProfile::factory()->create()->user;

    actingAs($owner)
        ->post(route('owner.setup.store'), [
            'shop_name' => 'Another shop',
            'meetup_area' => 'Cubao',
            'phone' => '09171234567',
        ])
        ->assertRedirect(route('owner.dashboard'));

    expect(OwnerProfile::count())->toBe(1);
});

test('shop name and meetup area are required', function (string $field) {
    actingAs(User::factory()->create())
        ->post(route('owner.setup.store'), [
            'shop_name' => '',
            'meetup_area' => '',
            'phone' => '09171234567',
        ])
        ->assertSessionHasErrors($field);
})->with(['shop_name', 'meetup_area']);

test('owners can see their dashboard', function () {
    $owner = OwnerProfile::factory()->create()->user;

    actingAs($owner)->get(route('owner.dashboard'))->assertOk();
});
