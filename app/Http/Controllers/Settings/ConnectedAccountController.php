<?php

namespace App\Http\Controllers\Settings;

use App\Concerns\PasswordValidationRules;
use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConnectedAccountController extends Controller
{
    use PasswordValidationRules;

    public function edit(Request $request): Response
    {
        $linked = $request->user()->socialAccounts()->pluck('provider')->map->value->all();

        return Inertia::render('settings/connected-accounts', [
            'providers' => collect(SocialProvider::cases())->map(fn (SocialProvider $provider) => [
                'value' => $provider->value,
                'label' => $provider->label(),
                'connected' => in_array($provider->value, $linked, true),
            ]),
            'hasPassword' => $request->user()->password !== null,
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]);
    }

    /**
     * Disconnect a provider, unless it is the last way to log in.
     */
    public function destroy(Request $request, SocialProvider $provider): RedirectResponse
    {
        $user = $request->user();

        if ($user->password === null && $user->socialAccounts()->count() <= 1) {
            throw ValidationException::withMessages([
                'provider' => __('This is your only way to log in. Set a password first, then disconnect :provider.', ['provider' => $provider->label()]),
            ]);
        }

        $user->socialAccounts()->where('provider', $provider)->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':provider disconnected.', ['provider' => $provider->label()])]);

        return back();
    }

    /**
     * Let users who signed up with a provider add a password.
     */
    public function setPassword(Request $request): RedirectResponse
    {
        if ($request->user()->password !== null) {
            throw ValidationException::withMessages(['password' => __('You already have a password. Change it under Security.')]);
        }

        $validated = $request->validate(['password' => $this->passwordRules()]);

        $request->user()->update(['password' => $validated['password']]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password set. You can now log in with your email.')]);

        return back();
    }
}
