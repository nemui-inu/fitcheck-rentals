<?php

namespace App\Http\Controllers\Api;

use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckAvailabilityRequest;
use App\Http\Requests\IndexItemsRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Support\Availability;
use App\Support\MarketplaceQuery;
use App\Support\RentalDays;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ItemController extends Controller
{
    public function index(IndexItemsRequest $request): AnonymousResourceCollection
    {
        $items = MarketplaceQuery::apply(Item::listed(), $request->validated())
            ->with(['category', 'images', 'ownerProfile'])
            ->latest()
            ->paginate(12);

        return ItemResource::collection($items);
    }

    public function show(int $item): ItemResource
    {
        return new ItemResource(
            Item::listed()
                ->with(['category', 'images', 'ownerProfile'])
                ->withCount(['units as available_units_count' => fn (Builder $query) => $query->where('status', UnitStatus::Active)])
                ->findOrFail($item)
        );
    }

    public function availability(CheckAvailabilityRequest $request, int $item): JsonResponse
    {
        $item = Item::listed()->findOrFail($item);

        $start = CarbonImmutable::parse($request->validated('start'), RentalDays::TIMEZONE);
        $end = CarbonImmutable::parse($request->validated('end'), RentalDays::TIMEZONE);

        $freeUnits = $item->units
            ->filter(fn (ItemUnit $unit) => Availability::isFree($unit, $start, $end))
            ->count();

        return response()->json([
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'available' => $freeUnits > 0,
            'free_units' => $freeUnits,
        ]);
    }
}
