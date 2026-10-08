<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

class SocialiteController extends Controller
{
    public function redirect(SocialProvider $provider): SymfonyRedirect
    {
        return Socialite::driver($provider->value)->redirect();
    }

    /**
     * Link the provider when logged in, otherwise log in or sign up.
     */
    public function callback(Request $request, SocialProvider $provider): RedirectResponse
    {
        try {
            $providerUser = Socialite::driver($provider->value)->user();
        } catch (Throwable) {
            return to_route('login')->withErrors(['email' => __(':provider login did not finish. Try again.', ['provider' => $provider->label()])]);
        }

        $account = SocialAccount::with('user')
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUser->getId())
            ->first();

        if ($request->user() !== null) {
            return $this->link($request->user(), $provider, $providerUser, $account);
        }

        if ($account !== null) {
            return $this->logIn($request, $account->user);
        }

        $email = $providerUser->getEmail();

        if ($email === null) {
            return to_route('login')->withErrors(['email' => __(':provider did not share an email. Sign up with email and password instead.', ['provider' => $provider->label()])]);
        }

        if (User::where('email', $email)->exists()) {
            return to_route('login')->withErrors(['email' => __('An account with this email already exists. Log in with your password, then connect :provider in Settings.', ['provider' => $provider->label()])]);
        }

        $user = DB::transaction(function () use ($provider, $providerUser, $email) {
            $user = new User;
            $user->name = $providerUser->getName() ?? $email;
            $user->email = $email;
            $user->email_verified_at = now();
            $user->password = null;
            $user->save();

            $user->socialAccounts()->create(['provider' => $provider, 'provider_user_id' => $providerUser->getId()]);

            return $user;
        });

        return $this->logIn($request, $user);
    }

    private function link(User $user, SocialProvider $provider, ProviderUser $providerUser, ?SocialAccount $account): RedirectResponse
    {
        if ($account !== null && $account->user_id !== $user->id) {
            $message = __('That :provider account is connected to another user.', ['provider' => $provider->label()]);
        } elseif ($account === null && $user->socialAccounts()->where('provider', $provider)->exists()) {
            $message = __('You already connected a different :provider account. Disconnect it first.', ['provider' => $provider->label()]);
        } else {
            $user->socialAccounts()->firstOrCreate(['provider' => $provider, 'provider_user_id' => $providerUser->getId()]);

            Inertia::flash('toast', ['type' => 'success', 'message' => __(':provider connected.', ['provider' => $provider->label()])]);

            return to_route('connected-accounts.edit');
        }

        Inertia::flash('toast', ['type' => 'error', 'message' => $message]);

        return to_route('connected-accounts.edit');
    }

    private function logIn(Request $request, User $user): RedirectResponse
    {
        if ($user->isSuspended()) {
            return to_route('login')->withErrors(['email' => __('This account is suspended. Contact an admin if you think this is a mistake.')]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
