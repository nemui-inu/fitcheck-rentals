<?php

namespace App\Actions;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteItem
{
    /**
     * Delete an item with its units and photos, then remove the files.
     */
    public function __invoke(Item $item): void
    {
        $paths = $item->images()->pluck('path')->all();

        DB::transaction(function () use ($item) {
            $item->units()->delete();
            $item->delete();
        });

        Storage::disk('public')->delete($paths);
    }
}
