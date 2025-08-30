<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function booking($request)
    {
        $request->validate([
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
            'booking_date' => 'required|date',
            'notes' => 'string',
        ]);


        $booking = Booking::query()->create([
            'user_id' => Auth::id(),
            'booking_date' => $request->booking_date,
            'notes' => $request->notes,
        ]);

        foreach ($request->services as $serviceId) {
            $service = Service::find($serviceId);

            if (!$service->is_bookable) {
                return ['booking' => null, 'message' => "Service {$service->name} is not bookable"];
            }

            $service->increment('booking_count');
            $booking->services()->attach($serviceId);
        }
        $bookingTime = Carbon::parse($request->booking_date);
        $startTime = $bookingTime->copy()->setTime(8,0);
        $endTime = $bookingTime->copy()->setTime(20,0);
        if ($bookingTime->lt($startTime)) {
            return ['booking' => null, 'message' => 'you cant book before 8 am clock'];
        }
        if($bookingTime->gt($endTime)){
            return ['booking' => null, 'message' => 'you cant book after 8 pm clock'];
        }

        return ['booking' => $booking->load('services'),'message' => 'Booking has been created'];

    }

    public function updateBooking($request)
    {
        $booking = Booking::query()->find($request->booking_id);
        if(!$booking){
            return ['booking' => null, 'message' => 'Booking not found'];
        }

        if(Auth::user()->hasRole('client') && Auth::id() == $booking->user_id || Auth::user()->hasRole(['admin','receptionist']) ){

            $request->validate([
                'services' => 'required|array',
                'services.*' => 'exists:services,id',
                'booking_date' => 'required|date',
                'notes' => 'string|nullable',
            ]);

            $booking->update([
                'booking_date' => $request->booking_date,
                'notes' => $request->notes,
            ]);

            $booking->services()->sync($request->services);
            return ['booking' => $booking->load('services'),'message' => 'Booking has been updated'];
        }else {
            return ['booking' => null, 'message' => 'You are not allowed to update booking'];
        }

    }

   public function deleteBooking($bookingId){
        $booking = Booking::query()->find($bookingId);

        if(!$booking){
            return ['booking' => null, 'message' => 'Booking not found'];
        }
        elseif ($booking->status == 'cancelled'){
            return ['booking' => null, 'message' => 'Booking has been cancelled'];
        }
        elseif (Auth::user()->hasRole('client') && Auth::id() == $booking->user_id || Auth::user()->hasRole(['admin','receptionist']) ) {
            $booking->update([
                'status' => 'cancelled'
                ]);
            $booking->service()->decrement('booking_count');
            return ['booking' => $booking,'message' => 'Booking has been cancelled'];
        }
        else {
            return ['booking' => null, 'message' => 'You are not allowed to delete booking'];
        }
   }

   //booking by receptionist
   public function storeBooking($request): array
   {
       $request->validate([
           'user_id' => 'required|exists:users,id',
           'services' => 'required|array',
           'services.*' => 'exists:services,id',
           'booking_date' => 'required|date',
           'notes' => 'string',
       ]);


       $booking = Booking::query()->create([
           'user_id' => $request->user_id,
           'booking_date' => $request->booking_date,
           'notes' => $request->notes,
       ]);

       foreach ($request->services as $serviceId) {
           $service = Service::find($serviceId);

           if (!$service->is_bookable) {
               return ['booking' => null, 'message' => "Service {$service->name} is not bookable"];
           }

           $service->increment('booking_count');
           $booking->services()->attach($serviceId);
       }
       $bookingTime = Carbon::parse($request->booking_date);
       $startTime = $bookingTime->copy()->setTime(8,0);
       $endTime = $bookingTime->copy()->setTime(20,0);
       if ($bookingTime->lt($startTime)) {
           return ['booking' => null, 'message' => 'you cant book before 8 am clock'];
       }
       if($bookingTime->gt($endTime)){
           return ['booking' => null, 'message' => 'you cant book after 8 pm clock'];
       }

       return ['booking' => $booking->load('services'),'message' => 'Booking has been created'];
   }


}
