<?php

use App\Support\RentalDays;
use Carbon\CarbonImmutable;

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
