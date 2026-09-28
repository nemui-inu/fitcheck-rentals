<?php

use App\Support\RentalDays;
use Carbon\CarbonImmutable;

describe('count', function () {
    it('counts rental days', function (string $start, string $end, int $days) {
        $start = CarbonImmutable::parse($start);
        $end = CarbonImmutable::parse($end);

        expect(RentalDays::count($start, $end))->toBe($days);
    })->with([
        'same day' => ['2026-10-12', '2026-10-12', 1],
        'different days' => ['2026-10-12', '2026-10-14', 3],
        'month boundary' => ['2026-10-30', '2026-11-02', 4],
        'time of day ignored' => ['2026-10-12 23:59', '2026-10-13 00:01', 2],
    ]);

    it('rejects an end date set before the start date', function () {
        $start = CarbonImmutable::parse('2026-10-14 23:59');
        $end = CarbonImmutable::parse('2026-10-13 00:01');

        RentalDays::count($start, $end);
    })->throws(InvalidArgumentException::class);
});

describe('late', function () {
    it('counts late days', function (string $end, string $returnedAt, int $days) {
        $end = CarbonImmutable::parse($end, RentalDays::TIMEZONE);
        $returnedAt = CarbonImmutable::parse($returnedAt, RentalDays::TIMEZONE);

        expect(RentalDays::late($end, $returnedAt))->toBe($days);
    })->with([
        'returned on the same day' => ['2026-10-14', '2026-10-14 21:00', 0],
        'returned late' => ['2026-10-14', '2026-10-17 23:59', 3],
        'returned early' => ['2026-10-14', '2026-10-12 07:00', 0],
        'returned using a UTC return date' => ['2026-10-14', '2026-10-14T17:00+00:00', 1],
        'returned using an Asia/Manila return date' => ['2026-10-14', '2026-10-14T17:00+08:00', 0],
        'end date using different timezone' => ['2026-10-14T20:00:00-05:00', '2026-10-15 10:00', 1],
    ]);
});
