<?php

namespace App\Http\Controllers\Owner;

use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ItemUnitRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemUnitController extends Controller
{
    public function store(ItemUnitRequest $request, int $item): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);

        $item->units()->create($request->safe()->only(['label', 'condition']));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Unit added.')]);

        return back();
    }

    public function update(ItemUnitRequest $request, int $item, int $unit): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);
        $unit = $item->units()->findOrFail($unit);

        $unit->fill($request->safe()->only(['label', 'condition']));

        if ($request->has('status')) {
            $unit->status = UnitStatus::from($request->validated('status'));
        }

        $unit->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Unit saved.')]);

        return back();
    }

    public function destroy(Request $request, int $item, int $unit): RedirectResponse
    {
        $item = $request->user()->ownerProfile->items()->findOrFail($item);
        $unit = $item->units()->findOrFail($unit);

        if ($unit->bookings()->exists()) {
            Inertia::flash('toast', ['type' => 'error', 'message' => __('This unit has bookings, so it cannot be deleted. Retire it instead.')]);

            return back();
        }

        $unit->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Unit deleted.')]);

        return back();
    }
}
