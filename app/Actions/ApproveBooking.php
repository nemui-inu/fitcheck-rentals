<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Support\Availability;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveBooking
{
    /**
     * Recheck the assigned unit inside a transaction, swapping to another free unit if needed.
     *
     * @throws ValidationException
     */
    public function __invoke(Booking $booking): Booking
    {
        if (! $booking->status->canTransitionTo(BookingStatus::Approved, 'owner')) {
            throw ValidationException::withMessages(['status' => __('Only pending requests can be approved.')]);
        }

        return DB::transaction(function () use ($booking) {
            $item = $booking->unit->item;
            $item->units()->lockForUpdate()->get();

            $unit = $booking->unit->fresh();

            if ($unit === null || ! Availability::isFree($unit, $booking->start_date, $booking->end_date)) {
                $unit = Availability::firstFreeUnit($item, $booking->start_date, $booking->end_date);
            }

            if ($unit === null) {
                throw ValidationException::withMessages(['status' => __('No unit is free for these dates anymore. Reject the request or free up a unit.')]);
            }

            $booking->item_unit_id = $unit->id;
            $booking->status = BookingStatus::Approved;
            $booking->save();

            return $booking;
        });
    }
}
