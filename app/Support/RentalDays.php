<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

class RentalDays
{
    public static function count(DateTimeInterface $start, DateTimeInterface $end): int
    {
        $start = CarbonImmutable::instance($start)->startOfDay();
        $end = CarbonImmutable::instance($end)->startOfDay();

        return (int) $start->diffInDays($end) + 1;
    }
}
