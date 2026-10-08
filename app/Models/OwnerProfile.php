<?php

namespace App\Models;

use Database\Factories\OwnerProfileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $shop_name
 * @property string $meetup_area
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['shop_name', 'meetup_area'])]
class OwnerProfile extends Model
{
    /** @use HasFactory<OwnerProfileFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Item, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /** @return HasManyThrough<ItemUnit, Item, $this> */
    public function units(): HasManyThrough
    {
        return $this->hasManyThrough(ItemUnit::class, Item::class);
    }

    /**
     * Bookings on any unit of this owner's items.
     *
     * @return Builder<Booking>
     */
    public function bookings(): Builder
    {
        return Booking::query()->whereRelation('unit.item', 'owner_profile_id', $this->id);
    }
}
