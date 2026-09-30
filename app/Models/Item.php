<?php

namespace App\Models;

use App\Enums\ItemStatus;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_profile_id
 * @property int $category_id
 * @property string $name
 * @property string|null $series
 * @property string|null $character
 * @property string|null $size
 * @property string|null $description
 * @property int $daily_rate
 * @property int $deposit
 * @property ItemStatus $status
 * @property Carbon|null $taken_down_at
 * @property int|null $taken_down_by
 * @property string|null $takedown_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category_id', 'name', 'series', 'character', 'size', 'description'])]
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => ItemStatus::Draft->value,
        'series' => null,
        'character' => null,
        'size' => null,
        'description' => null,
        'taken_down_at' => null,
        'taken_down_by' => null,
        'takedown_reason' => null,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ItemStatus::class,
            'daily_rate' => 'integer',
            'deposit' => 'integer',
            'taken_down_at' => 'datetime',
        ];
    }

    /**
     * Items renters can see: active, from owners who are not suspended.
     *
     * @param  Builder<Item>  $query
     */
    public function scopeListed(Builder $query): void
    {
        $query->where('status', ItemStatus::Active)
            ->whereHas('ownerProfile.user', fn (Builder $user) => $user->whereNull('suspended_at'));
    }

    /** @return BelongsTo<OwnerProfile, $this> */
    public function ownerProfile(): BelongsTo
    {
        return $this->belongsTo(OwnerProfile::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<ItemImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class)->orderBy('sort_order');
    }

    /** @return HasOne<ItemImage, $this> */
    public function coverImage(): HasOne
    {
        return $this->hasOne(ItemImage::class)->ofMany('sort_order', 'min');
    }

    /** @return HasMany<ItemUnit, $this> */
    public function units(): HasMany
    {
        return $this->hasMany(ItemUnit::class);
    }
}
