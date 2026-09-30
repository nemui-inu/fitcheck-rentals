<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? null;

        return Inertia::render('admin/users/index', [
            'users' => User::query()
                ->with('ownerProfile:id,user_id,shop_name')
                ->when($search, fn ($query) => $query->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('Admins cannot be suspended.')]);

            return back();
        }

        $user->suspended_at = now();
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name is suspended. Their items are hidden.', ['name' => $user->name])]);

        return back();
    }

    public function unsuspend(User $user): RedirectResponse
    {
        $user->suspended_at = null;
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name can log in again.', ['name' => $user->name])]);

        return back();
    }
}
