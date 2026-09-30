<?php

use App\Enums\SocialProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;

function fakeProvider(string $provider = 'google', array $attributes = []): void
{
    Socialite::fake($provider, ProviderUser::fake(['id' => 'g-123', 'email' => 'cos@example.com', 'name' => 'Cos Player', ...$attributes]));
}

test('redirects to the provider', function (string $provider) {
    Socialite::fake($provider);

    get(route('social.redirect', $provider))->assertRedirect();
})->with(['google', 'facebook']);

test('unknown providers are not found', function () {
    get('/auth/twitter/redirect')->assertNotFound();
});

test('linked accounts log in', function () {
    $account = SocialAccount::factory()->create(['provider' => SocialProvider::Google, 'provider_user_id' => 'g-123']);
    fakeProvider();

    get(route('social.callback', 'google'))->assertRedirect(route('dashboard'));

    assertAuthenticatedAs($account->user);
});

test('new emails create a user without a password', function () {
    fakeProvider('facebook');

    get(route('social.callback', 'facebook'))->assertRedirect(route('dashboard'));

    $user = User::firstWhere('email', 'cos@example.com');

    expect($user->password)->toBeNull()
        ->and($user->socialAccounts()->sole()->provider)->toBe(SocialProvider::Facebook);
    assertAuthenticatedAs($user);
});

test('existing emails without the provider linked are refused', function () {
    User::factory()->create(['email' => 'cos@example.com']);
    fakeProvider();

    get(route('social.callback', 'google'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => 'An account with this email already exists. Log in with your password, then connect Google in Settings.']);

    assertGuest();
    expect(SocialAccount::count())->toBe(0);
});

test('suspended users cannot log in with a provider', function () {
    $user = User::factory()->suspended()->create();
    SocialAccount::factory()->for($user)->create(['provider_user_id' => 'g-123']);
    fakeProvider();

    get(route('social.callback', 'google'))->assertSessionHasErrors('email');

    assertGuest();
});

test('logged in users connect a provider', function () {
    $user = User::factory()->create();
    fakeProvider();

    actingAs($user)->get(route('social.callback', 'google'))->assertRedirect(route('connected-accounts.edit'));

    expect($user->socialAccounts()->sole()->provider_user_id)->toBe('g-123');
});

test('a provider account linked to someone else cannot be connected', function () {
    SocialAccount::factory()->create(['provider_user_id' => 'g-123']);
    $user = User::factory()->create();
    fakeProvider();

    actingAs($user)->get(route('social.callback', 'google'));

    expect($user->socialAccounts()->count())->toBe(0);
});

test('connected accounts page renders', function () {
    actingAs(User::factory()->create())->get(route('connected-accounts.edit'))->assertOk();
});

test('users with a password can disconnect a provider', function () {
    $account = SocialAccount::factory()->create();

    actingAs($account->user)
        ->delete(route('connected-accounts.destroy', 'google'))
        ->assertSessionHasNoErrors();

    expect(SocialAccount::count())->toBe(0);
});

test('the last login method cannot be disconnected', function () {
    $user = User::factory()->create(['password' => null]);
    SocialAccount::factory()->for($user)->create();

    actingAs($user)
        ->delete(route('connected-accounts.destroy', 'google'))
        ->assertSessionHasErrors('provider');

    expect(SocialAccount::count())->toBe(1);
});

test('social only users can set a password once', function () {
    $user = User::factory()->create(['password' => null]);

    actingAs($user)
        ->put(route('connected-accounts.set-password'), ['password' => 'new-password', 'password_confirmation' => 'new-password'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->password)->not->toBeNull();

    actingAs($user->fresh())
        ->put(route('connected-accounts.set-password'), ['password' => 'other-pass', 'password_confirmation' => 'other-pass'])
        ->assertSessionHasErrors('password');
});

test('users without a password cannot log in with the password form', function () {
    $user = User::factory()->create(['password' => null]);

    $this->post(route('login.store'), ['email' => $user->email, 'password' => ''])->assertSessionHasErrors();

    assertGuest();
});
