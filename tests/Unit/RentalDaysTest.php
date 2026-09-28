<?php

use App\Support\RentalDays;
use Carbon\CarbonImmutable;

it('counts as 1 day when the rental starts and end on the same day', function () {
    $start = CarbonImmutable::parse('2026-10-12');
    $end = CarbonImmutable::parse('2026-10-12');

    $days = RentalDays::count($start, $end);

    expect($days)->toBe(1);
});

it('counts the rental duration from start to end', function () {
    $start = CarbonImmutable::parse('2026-10-12');
    $end = CarbonImmutable::parse('2026-10-14');

    $days = RentalDays::count($start, $end);

    expect($days)->toBe(3);
});

it('counts the days regardless of the time of day', function () {
    $start = CarbonImmutable::parse('2026-10-12 23:59');
    $end = CarbonImmutable::parse('2026-10-13 00:01');

    $days = RentalDays::count($start, $end);

    expect($days)->toBe(2);
});
