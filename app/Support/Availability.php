<?php

namespace App\Support;

use App\Enums\BookingStatus;
use App\Enums\UnitStatus;
use App\Models\Item;
use App\Models\ItemUnit;
use DateTimeInterface;

class Availability
{
    /**
     * A unit is free when it is active and no approved or active booking overlaps the range.
     */
    public static function isFree(ItemUnit $unit, DateTimeInterface $start, DateTimeInterface $end): bool
    {
        return $unit->status === UnitStatus::Active
            && ! $unit->bookings()
                ->whereIn('status', BookingStatus::blocking())
                ->whereDate('start_date', '<=', $end->format('Y-m-d'))
                ->whereDate('end_date', '>=', $start->format('Y-m-d'))
                ->exists();
    }

    /**
     * First free unit of an item, by id. Pass $lock inside a transaction to lock the units.
     */
    public static function firstFreeUnit(Item $item, DateTimeInterface $start, DateTimeInterface $end, bool $lock = false): ?ItemUnit
    {
        return $item->units()
            ->where('status', UnitStatus::Active)
            ->orderBy('id')
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->get()
            ->first(fn (ItemUnit $unit) => self::isFree($unit, $start, $end));
    }
}
