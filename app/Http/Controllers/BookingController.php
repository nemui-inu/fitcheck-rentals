<?php

namespace App\Http\Controllers;

use App\Actions\ChangeBookingStatus;
use App\Actions\RequestBooking;
use App\Enums\BookingStatus;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Item;
use App\Support\RentalDays;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('bookings/index', [
            'bookings' => $request->user()->bookings()
                ->with(['unit.item.coverImage'])
                ->latest()
                ->get(),
        ]);
    }

    public function show(Request $request, string $booking): Response
    {
        $booking = $request->user()->bookings()
            ->with(['unit.item.coverImage', 'unit.item.ownerProfile.user:id,name,phone'])
            ->where('reference', $booking)
            ->firstOrFail();

        $daysLate = match (true) {
            $booking->returned_at !== null => RentalDays::late($booking->end_date, $booking->returned_at),
            $booking->status === BookingStatus::Active => RentalDays::late($booking->end_date, now()),
            default => 0,
        };

        return Inertia::render('bookings/show', [
            'booking' => $booking,
            'daysLate' => $daysLate,
        ]);
    }

    public function store(StoreBookingRequest $request, int $item, RequestBooking $requestBooking): RedirectResponse
    {
        $item = Item::listed()->findOrFail($item);
        $renter = $request->user();

        if ($request->filled('phone') && $renter->phone === null) {
            $renter->phone = $request->string('phone')->toString();
            $renter->save();
        }

        $booking = $requestBooking(
            $renter,
            $item,
            CarbonImmutable::parse($request->validated('start_date'), RentalDays::TIMEZONE),
            CarbonImmutable::parse($request->validated('end_date'), RentalDays::TIMEZONE),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Request sent. The owner will approve or reject it.')]);

        return to_route('bookings.show', $booking->reference);
    }

    public function cancel(Request $request, string $booking, ChangeBookingStatus $changeStatus): RedirectResponse
    {
        $booking = $request->user()->bookings()->where('reference', $booking)->firstOrFail();

        $changeStatus($booking, BookingStatus::Cancelled, 'renter');

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Booking cancelled.')]);

        return back();
    }
}
