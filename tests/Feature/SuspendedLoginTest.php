<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\post;

test('suspended users cannot log in with a password', function () {
    $user = User::factory()->suspended()->create();

    post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    assertGuest();
});

test('active users still log in', function () {
    $user = User::factory()->create();

    post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasNoErrors();

    expect(auth()->id())->toBe($user->id);
});

test('users suspended mid session are logged out on their next request', function () {
    $user = User::factory()->create();

    actingAs($user)->get(route('dashboard'))->assertOk();

    $user->forceFill(['suspended_at' => now()])->save();

    actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    assertGuest();
});
