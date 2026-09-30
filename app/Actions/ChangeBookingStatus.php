<?php

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class ChangeBookingStatus
{
    /**
     * Apply a simple transition: reject, cancel, mark active, or mark returned.
     *
     * @param  'owner'|'renter'  $actor
     *
     * @throws ValidationException
     */
    public function __invoke(Booking $booking, BookingStatus $to, string $actor): Booking
    {
        if (! $booking->status->canTransitionTo($to, $actor)) {
            throw ValidationException::withMessages([
                'status' => __('This booking is :from and cannot be marked :to.', ['from' => $booking->status->value, 'to' => $to->value]),
            ]);
        }

        $booking->status = $to;

        if ($to === BookingStatus::Returned) {
            $booking->returned_at = now();
        }

        $booking->save();

        return $booking;
    }
}
