<?php

use App\Models\OwnerProfile;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('owners are flagged as owners', function () {
    $owner = OwnerProfile::factory()->create()->user;

    actingAs($owner)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('isOwner', true)
            ->where('isAdmin', false));
});

test('admins are flagged as admins', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('isOwner', false)
            ->where('isAdmin', true));
});

test('guests are not flagged', function () {
    get(route('home'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('isOwner', false)
            ->where('isAdmin', false));
});
