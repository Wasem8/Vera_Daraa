<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function booking($request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'notes' => 'string',
        ]);
        $service = Service::query()->find($request->service_id);
        if(!$service->is_bookable){
            return ['booking' => null, 'message' => 'Service is not bookable'];
        }
        $service->booking_count +=1;
        $service->save();
        $booking = Booking::query()->create([
            'user_id' => Auth::user()->id,
            'service_id' => $service->id,
            'booking_date' => $request->booking_date,
            'notes' => $request->notes,

        ]);
        return ['booking' => $booking,'message' => 'Booking has been created'];

    }

    public function updateBooking($request)
    {
        $booking = Booking::query()->find($request->booking_id);
        if(!$booking){
            return ['booking' => null, 'message' => 'Booking not found'];
        }
        $booking->update([
            'booking_date' => $request->booking_date,
            'notes' => $request->notes,
        ]);
        return ['booking' => $booking,'message' => 'Booking has been updated'];
    }
}
