<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Item;
use App\Models\User;
use App\Support\Availability;
use App\Support\RentalDays;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestBooking
{
    /**
     * Create a pending booking on the first free unit of the item.
     *
     * @throws ValidationException
     */
    public function __invoke(User $renter, Item $item, CarbonImmutable $start, CarbonImmutable $end): Booking
    {
        if ($renter->ownerProfile?->id === $item->owner_profile_id) {
            throw ValidationException::withMessages(['start_date' => __('You cannot book your own item.')]);
        }

        $hasPending = $renter->bookings()
            ->where('status', BookingStatus::Pending)
            ->whereRelation('unit', 'item_id', $item->id)
            ->exists();

        if ($hasPending) {
            throw ValidationException::withMessages(['start_date' => __('You already have a pending request for this item. Wait for the owner or cancel it first.')]);
        }

        return DB::transaction(function () use ($renter, $item, $start, $end) {
            $unit = Availability::firstFreeUnit($item, $start, $end, lock: true);

            if ($unit === null) {
                throw ValidationException::withMessages(['start_date' => __('Those dates are fully booked. Pick another range.')]);
            }

            $booking = new Booking;
            $booking->item_unit_id = $unit->id;
            $booking->renter_id = $renter->id;
            $booking->start_date = $start;
            $booking->end_date = $end;
            $booking->total = RentalDays::count($start, $end) * $item->daily_rate;
            $booking->deposit = $item->deposit;
            $booking->save();

            return $booking;
        });
    }
}
