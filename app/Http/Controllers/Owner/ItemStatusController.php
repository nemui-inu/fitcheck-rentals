<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ItemStatusController extends Controller
{
    /**
     * Publish or pause an item.
     */
    public function __invoke(Request $request, int $item): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->withCount('units')->findOrFail($item);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(ItemStatus::class)->only(ItemStatus::ownerSettable())],
        ]);
        $to = ItemStatus::from($validated['status']);

        if (! $item->status->ownerCanTransitionTo($to)) {
            throw ValidationException::withMessages([
                'status' => __('A :from item cannot be set to :to.', ['from' => $item->status->value, 'to' => $to->value]),
            ]);
        }

        if ($to === ItemStatus::Active && $item->units_count === 0) {
            throw ValidationException::withMessages([
                'status' => __('Add at least one unit before publishing.'),
            ]);
        }

        $item->status = $to;
        $item->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => $to === ItemStatus::Active ? __('Item is live in the marketplace.') : __('Item paused.')]);

        return back();
    }

    /**
     * Send a taken down item back to admins for review.
     */
    public function resubmit(Request $request, int $item): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);

        if (! $item->status->canTransitionTo(ItemStatus::PendingReview)) {
            throw ValidationException::withMessages(['status' => __('Only taken down items can be resubmitted.')]);
        }

        $item->status = ItemStatus::PendingReview;
        $item->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Sent for review. An admin will approve it or explain what to fix.')]);

        return back();
    }
}
