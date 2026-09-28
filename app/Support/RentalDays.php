<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class RentalDays
{
    public const TIMEZONE = 'Asia/Manila';

    private static function calendarDate(
        DateTimeInterface $date
    ): CarbonImmutable {
        return CarbonImmutable::parse(
            $date->format('Y-m-d'),
            self::TIMEZONE
        );
    }

    /**
     * Rental length in days, counting both the start and end date.
     *
     * Both are calendar dates: their time and offset are ignored.
     *
     * @throws InvalidArgumentException when end date is set before start.
     **/
    public static function count(
        DateTimeInterface $start,
        DateTimeInterface $end
    ): int {
        $start = self::calendarDate($start);
        $end = self::calendarDate($end);

        if ($end->lessThan($start)) {
            throw new InvalidArgumentException(
                'End date must be set later than the start date.'
            );
        }

        return (int) $start->diffInDays($end) + 1;
    }

    /**
     * Days past the end date.
     *
     * $end is a calendar date: its time and offsets are ignored.
     * $returnedAt is a moment: it's read in Manila time.
     **/
    public static function late(
        DateTimeInterface $end,
        DateTimeInterface $returnedAt
    ): int {
        $end = self::calendarDate($end);
        $returnedAt = CarbonImmutable::instance($returnedAt)
            ->setTimezone(self::TIMEZONE)
            ->startOfDay();

        return max((int) $end->diffInDays($returnedAt), 0);
    }
}
