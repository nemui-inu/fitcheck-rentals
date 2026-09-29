<?php

use App\Enums\Role;
use App\Models\User;

test('new users get the user role by default', function () {
    $user = User::factory()->create();

    expect($user->fresh()->role)->toBe(Role::User);
});
