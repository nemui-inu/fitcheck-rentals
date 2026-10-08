<?php

namespace App\Http\Controllers\Owner;

use App\Actions\DeleteItem;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ItemRequest;
use App\Models\Category;
use App\Support\UnitLabel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('owner/items/index', [
            'items' => $request->user()->ownerProfile->items()
                ->with(['category', 'coverImage'])
                ->withCount('units')
                ->latest()
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('owner/items/create', [
            'categories' => Category::active()->orderBy('name')->get(),
        ]);
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->make($request->details());
        $item->forceFill($request->money())->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item saved as a draft. Add photos and units, then publish it.')]);

        return to_route('owner.items.edit', $item->id);
    }

    public function edit(Request $request, int $item): Response
    {
        $ownerProfile = $request->user()->ownerProfile;
        $item = $ownerProfile->items()->with(['category', 'images', 'units'])->findOrFail($item);

        return Inertia::render('owner/items/edit', [
            'item' => $item,
            'categories' => Category::active()->orWhere('id', $item->category_id)->orderBy('name')->get(),
            'suggestedLabel' => UnitLabel::suggest($ownerProfile, $item->category),
        ]);
    }

    public function update(ItemRequest $request, int $item): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);
        $item->fill($request->details())->forceFill($request->money())->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item saved.')]);

        return to_route('owner.items.edit', $item->id);
    }

    public function destroy(Request $request, int $item, DeleteItem $deleteItem): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);

        if ($item->bookings()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('This item has bookings, so it cannot be deleted. Pause it instead.')]);

            return back();
        }

        $deleteItem($item);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item deleted.')]);

        return to_route('owner.items.index');
    }
}
