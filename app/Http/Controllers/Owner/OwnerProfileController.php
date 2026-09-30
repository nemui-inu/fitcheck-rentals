<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OwnerProfileController extends Controller
{
    /**
     * Show the owner profile setup form.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()->isOwner()) {
            return to_route('owner.dashboard');
        }

        return Inertia::render('owner/setup', [
            'hasPhone' => $request->user()->phone !== null,
        ]);
    }

    /**
     * Create the owner profile, saving the phone number if one was given.
     */
    public function store(StoreOwnerProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isOwner()) {
            return to_route('owner.dashboard');
        }

        DB::transaction(function () use ($request, $user) {
            if ($request->filled('phone')) {
                $user->phone = $request->string('phone')->toString();
                $user->save();
            }

            $user->ownerProfile()->create($request->safe()->only(['shop_name', 'meetup_area']));
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your shop is ready. Add your first item.')]);

        return to_route('owner.dashboard');
    }
}
