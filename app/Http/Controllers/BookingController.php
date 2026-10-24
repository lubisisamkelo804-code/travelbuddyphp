<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        return view('bookings', [
            'bookings' => Booking::with(['destination', 'event'])->where('user_id', Auth::id())->latest()->get(),
        ]);
    }

    public function cancel(Booking $booking)
    {
        abort_unless($booking->user_id === Auth::id(), 403);
        if ($booking->status === 'cancelled') return back()->with('error', 'Already cancelled.');

        $booking->update(['status' => 'cancelled']);

        if ($booking->destination && $booking->destination->booking_count > 0) $booking->destination->decrement('booking_count');
        if ($booking->event && $booking->event->booking_count > 0) $booking->event->decrement('booking_count');

        return back()->with('success', 'Booking cancelled.');
    }
}