<?php

use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

test('a user can have an owner profile', function () {
    $user = User::factory()->create();
    $profile = OwnerProfile::factory()->for($user)->create();

    expect($user->ownerProfile->is($profile))->toBeTrue();
});

test('a user cannot have more than one owner profile', function () {
    $user = User::factory()->create();

    OwnerProfile::factory()->for($user)->create();
    OwnerProfile::factory()->for($user)->create();
})->throws(UniqueConstraintViolationException::class);
