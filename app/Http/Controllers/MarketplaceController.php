<?php

namespace App\Http\Controllers;

use App\Enums\UnitStatus;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:6'],
            'size' => ['nullable', 'string', 'max:20'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $items = Item::listed()
            ->with(['category', 'coverImage'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(fn (Builder $inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('series', 'like', "%{$search}%")
                    ->orWhere('character', 'like', "%{$search}%"));
            })
            ->when($filters['category'] ?? null, fn (Builder $query, string $code) => $query->whereRelation('category', 'code', strtoupper($code)))
            ->when($filters['size'] ?? null, fn (Builder $query, string $size) => $query->where('size', $size))
            ->when(isset($filters['min_price']), fn (Builder $query) => $query->where('daily_rate', '>=', (int) round($filters['min_price'] * 100)))
            ->when(isset($filters['max_price']), fn (Builder $query) => $query->where('daily_rate', '<=', (int) round($filters['max_price'] * 100)))
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
