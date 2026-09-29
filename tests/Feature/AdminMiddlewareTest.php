<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'owner'])
        ->get('/_test/admin', fn () => 'ok');
});

test('guests are sent to login', function () {
    get('/_test/admin')->assertRedirect(route('login'));
});

test('normal users are forbidden', function () {
    actingAs(User::factory()->create())
        ->get('/_test/admin')
        ->assertForbidden();
});

test('admins get through', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get('/_test/admin')
        ->assertForbidden();
});
