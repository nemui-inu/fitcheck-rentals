<?php

use App\Models\OwnerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'owner'])
        ->get('/_test/owner', fn () => 'ok');
});

test('guests are sent to login', function () {
    get('/_test/owner')->assertRedirect(route('login'));
});

test('users without an owner profile are forbidden', function () {
    actingAs(User::factory()->create())
        ->get('/_test/owner')
        ->assertForbidden();
});

test('owners get through', function () {
    $owner = OwnerProfile::factory()->create()->user;

    actingAs($owner)->get('/_test/owner')->assertOk();
});
