<?php

namespace App\Support;

use App\Models\Category;
use App\Models\OwnerProfile;

class UnitLabel
{
    /**
     * Suggest the next label for an owner's unit in a category, like WIG-003.
     */
    public static function suggest(OwnerProfile $owner, Category $category): string
    {
        $prefix = $category->code.'-';

        $highest = $owner->units()
            ->where('item_units.label', 'like', $prefix.'%')
            ->pluck('item_units.label')
            ->map(fn (string $label): int => (int) substr($label, strlen($prefix)))
            ->max() ?? 0;

        return $prefix.str_pad((string) ($highest + 1), 3, '0', STR_PAD_LEFT);
    }
}
