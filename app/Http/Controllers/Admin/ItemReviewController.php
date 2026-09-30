<?php

namespace App\Http\Controllers\Admin;

use App\Actions\TakeDownItem;
use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ItemReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->validate([
            'status' => ['nullable', Rule::enum(ItemStatus::class)->only([ItemStatus::PendingReview, ItemStatus::Active, ItemStatus::TakenDown])],
        ])['status'] ?? ItemStatus::PendingReview->value;

        return Inertia::render('admin/items/index', [
            'items' => Item::query()
                ->where('status', $status)
                ->with(['category', 'coverImage', 'ownerProfile:id,shop_name'])
                ->latest('updated_at')
                ->paginate(20)
                ->withQueryString(),
            'status' => $status,
        ]);
    }

    public function takeDown(Request $request, Item $item, TakeDownItem $takeDown): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $takeDown($item, $request->user(), $validated['reason']);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item taken down. The owner sees your reason.')]);

        return back();
    }

    public function approve(Item $item): RedirectResponse
    {
        if ($item->status !== ItemStatus::PendingReview) {
            throw ValidationException::withMessages(['reason' => __('Only resubmitted items can be approved.')]);
        }

        $item->status = ItemStatus::Active;
        $item->taken_down_at = null;
        $item->taken_down_by = null;
        $item->takedown_reason = null;
        $item->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item is back in the marketplace.')]);

        return back();
    }
}
