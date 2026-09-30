<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the owner dashboard.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('owner/dashboard', [
            'profile' => $request->user()->ownerProfile,
        ]);
    }
}
