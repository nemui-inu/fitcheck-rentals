<?php

namespace App\Http\Controllers\Owner;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the owner dashboard with pending requests first.
     */
    public function __invoke(Request $request): Response
    {
        $profile = $request->user()->ownerProfile;

        return Inertia::render('owner/dashboard', [
            'profile' => $profile,
            'pendingBookings' => $profile->bookings()
                ->where('status', BookingStatus::Pending)
                ->with(['unit.item', 'renter:id,name,phone'])
                ->orderBy('start_date')
                ->get(),
            'counts' => [
                'items' => $profile->items()->count(),
                'upcoming' => $profile->bookings()->where('status', BookingStatus::Approved)->count(),
                'out' => $profile->bookings()->where('status', BookingStatus::Active)->count(),
            ],
        ]);
    }
}
