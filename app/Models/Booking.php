<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Carbon\CarbonImmutable;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $reference
 * @property int $item_unit_id
 * @property int $renter_id
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable $end_date
 * @property int $total
 * @property int $deposit
 * @property BookingStatus $status
 * @property CarbonImmutable|null $returned_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => BookingStatus::Pending->value,
        'returned_at' => null,
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->reference ??= (string) Str::ulid();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'returned_at' => 'datetime',
            'total' => 'integer',
            'deposit' => 'integer',
            'status' => BookingStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    /** @return BelongsTo<ItemUnit, $this> */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(ItemUnit::class, 'item_unit_id');
    }

    /** @return BelongsTo<User, $this> */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'renter_id');
    }
}
