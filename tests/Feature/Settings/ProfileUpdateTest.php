<?php

use App\Enums\SocialProvider;
use App\Models\Booking;
use App\Models\Item;
use App\Models\OwnerProfile;
use App\Models\SocialAccount;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});

test('passwordless user can delete their account without a password', function () {
    $user = User::factory()->create(['password' => null]);
    $user->socialAccounts()->create(['provider' => SocialProvider::Google, 'provider_user_id' => 'g-delete-me']);

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull()
        ->and(SocialAccount::where('provider_user_id', 'g-delete-me')->exists())->toBeFalse();
});

test('user with a shop cannot delete their account', function () {
    $user = User::factory()->create(['password' => null]);
    OwnerProfile::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh())->not->toBeNull();
});

test('user with bookings cannot delete their account', function () {
    $user = User::factory()->create(['password' => null]);
    Booking::factory()->create(['renter_id' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh())->not->toBeNull();
});

test('user who took down items cannot delete their account', function () {
    $user = User::factory()->create(['password' => null]);
    Item::factory()->create(['taken_down_by' => $user->id]);

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh())->not->toBeNull();
});

test('profile page reports whether the user has a password', function (?string $password, bool $hasPassword) {
    $user = User::factory()->create(['password' => $password]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/profile')
            ->where('hasPassword', $hasPassword),
        );
})->with([
    'password user' => ['password', true],
    'passwordless user' => [null, false],
]);
