<?php

namespace App\Http\Controllers\Owner;

use App\Actions\ApproveBooking;
use App\Actions\ChangeBookingStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->validate([
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
        ])['status'] ?? null;

        return Inertia::render('owner/bookings/index', [
            'bookings' => $request->user()->ownerProfile->bookings()
                ->with(['unit.item', 'renter:id,name,phone'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->orderBy('start_date')
                ->get(),
            'status' => $status,
        ]);
    }

    /**
     * Approve, reject, cancel, mark picked up, or mark returned.
     */
    public function update(Request $request, string $booking, ApproveBooking $approve, ChangeBookingStatus $changeStatus): RedirectResponse
    {
        $booking = $request->user()->ownerProfile->bookings()
            ->with('unit.item')
            ->where('reference', $booking)
            ->firstOrFail();

        $to = BookingStatus::from($request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)->except([BookingStatus::Pending])],
        ])['status']);

        if ($to === BookingStatus::Approved) {
            $approve($booking);
        } else {
            $changeStatus($booking, $to, 'owner');
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Booking marked :status.', ['status' => $to->value])]);

        return back();
    }
}
