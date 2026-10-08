<?php

namespace App\Http\Resources;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Item
 */
class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'series' => $this->series,
            'character' => $this->character,
            'size' => $this->size,
            'description' => $this->description,
            'daily_rate' => $this->daily_rate,
            'deposit' => $this->deposit,
            'category' => new CategoryResource($this->category),
            'cover_image' => $this->images->first()?->url,
            'images' => $this->images->map(fn (ItemImage $image): array => [
                'id' => $image->id,
                'sort_order' => $image->sort_order,
                'url' => $image->url,
            ]),
            'owner' => [
                'shop_name' => $this->ownerProfile?->shop_name,
                'meetup_area' => $this->ownerProfile?->meetup_area,
            ],
            'available_units_count' => $this->whenCounted('available_units_count'),
        ];
    }
}
