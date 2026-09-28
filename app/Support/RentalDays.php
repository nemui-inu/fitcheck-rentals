<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class RentalDays
{
    public static function count(DateTimeInterface $start, DateTimeInterface $end): int
    {
        $start = CarbonImmutable::instance($start)->startOfDay();
        $end = CarbonImmutable::instance($end)->startOfDay();

        if ($end->lessThan($start)) {
            throw new InvalidArgumentException('End date must be set later than the start date.');
        }

        return (int) $start->diffInDays($end) + 1;
    }

    public static function late(DateTimeInterface $end, DateTimeInterface $returnedAt): int
    {
        $end = CarbonImmutable::instance($end)->startOfDay();
        $returnedAt = CarbonImmutable::instance($returnedAt)->startOfDay();

        return max((int) $end->diffInDays($returnedAt), 0);
    }
}
