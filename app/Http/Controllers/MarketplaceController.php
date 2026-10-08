<?php

namespace App\Http\Controllers;

use App\Enums\UnitStatus;
use App\Http\Requests\IndexItemsRequest;
use App\Models\Category;
use App\Models\Item;
use App\Support\MarketplaceQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function index(IndexItemsRequest $request): Response
    {
        $filters = $request->validated();

        $items = MarketplaceQuery::apply(Item::listed(), $filters)
            ->with(['category', 'coverImage'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('marketplace/index', [
            'items' => $items,
            'filters' => $filters,
            'categories' => Category::active()->orderBy('name')->get(['id', 'name', 'code']),
            'sizes' => Item::listed()->whereNotNull('size')->distinct()->orderBy('size')->pluck('size'),
        ]);
    }

    public function show(Request $request, int $item): Response
    {
        $item = Item::listed()
            ->with(['category', 'images', 'ownerProfile:id,user_id,shop_name,meetup_area'])
            ->withCount(['units as available_units_count' => fn (Builder $query) => $query->where('status', UnitStatus::Active)])
            ->findOrFail($item);

        return Inertia::render('marketplace/show', [
            'item' => $item,
            'isOwnItem' => $request->user()?->ownerProfile?->id === $item->owner_profile_id,
        ]);
    }
}
