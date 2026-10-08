<?php

namespace App\Support;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceQuery
{
    /**
     * Apply the marketplace filters to a listed items query.
     *
     * @param  Builder<Item>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Item>
     */
    public static function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $inner, string $search) {
                $inner->where(fn (Builder $group) => $group
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('series', 'like', "%{$search}%")
                    ->orWhere('character', 'like', "%{$search}%"));
            })
            ->when($filters['category'] ?? null, fn (Builder $inner, string $code) => $inner->whereRelation('category', 'code', strtoupper($code)))
            ->when($filters['size'] ?? null, fn (Builder $inner, string $size) => $inner->where('size', $size))
            ->when(isset($filters['min_price']), fn (Builder $inner) => $inner->where('daily_rate', '>=', (int) round($filters['min_price'] * 100)))
            ->when(isset($filters['max_price']), fn (Builder $inner) => $inner->where('daily_rate', '<=', (int) round($filters['max_price'] * 100)));
    }
}
