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
            'user_id'    => 'nullable|exists:users,id',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
        ]);

        $bookingDate = Carbon::parse($request->booking_date);
        $serviceIds = $request->services;

        $availableSlots = $this->getAvailableSlots($bookingDate->toDateString(), $serviceIds);


        $isAvailable = collect($availableSlots)->contains(function ($slot) use ($bookingDate) {
            return $slot['start'] === $bookingDate->format('H:i');
        });

        if (!$isAvailable) {
            return [
                'status'  => 0,
                'booking'    => $availableSlots,
                'message' => "الوقت {$bookingDate->format('Y-m-d H:i')} غير متاح، الرجاء اختيار وقت آخر."
            ];
        }


        $booking = Booking::create([
                'user_id' => Auth::id(),
                'booking_date'=> $request->booking_date,
                'notes' => $request->notes,
                'status'  => 'pending',
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
                'client_phone' => 'sometimes|string|max:20',
                'notes' => 'string|nullable',
            ]);
            $bookingDate = Carbon::parse($request->booking_date);
            $serviceIds = $request->services;

            $availableSlots = $this->getAvailableSlots($bookingDate->toDateString(), $serviceIds);


            $isAvailable = collect($availableSlots)->contains(function ($slot) use ($bookingDate) {
                return $slot['start'] === $bookingDate->format('H:i');
            });

            if (!$isAvailable) {
                return [
                    'status'  => 0,
                    'booking'    => $availableSlots,
                    'message' => "الوقت {$bookingDate->format('Y-m-d H:i')} غير متاح، الرجاء اختيار وقت آخر."
                ];
            }

            $booking->update([
                'booking_date' => $request->booking_date,
                'client_phone' => $request->client_phone,
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
           'services' => 'required|array',
           'services.*' => 'exists:services,id',
           'booking_date' => 'required|date',
           'notes' => 'string',
           'user_id'    => 'nullable|exists:users,id',
           'customer_name'  => 'nullable|string|max:255',
           'customer_phone' => 'nullable|string|max:20',
       ]);

       $bookingDate       = Carbon::parse($request->booking_date);
       $serviceIds = $request->services;

       $availableSlots = $this->getAvailableSlots($bookingDate->toDateString(), $serviceIds);


       $isAvailable = collect($availableSlots)->contains(function ($slot) use ($bookingDate) {
           return $slot['start'] === $bookingDate->format('H:i');
       });

       if (!$isAvailable) {
           return [
               'status'  => 0,
               'booking'    => $availableSlots,
               'message' => "الوقت {$bookingDate->format('Y-m-d H:i')} غير متاح، الرجاء اختيار وقت آخر."
           ];
       }

       if ($request->filled('user_id')) {
           $booking = Booking::create([
               'user_id' => $request->user_id,
               'booking_date'=> $request->booking_date,
               'status'  => 'pending',
               'notes' => $request->notes,
           ]);
       }else {
           $booking = Booking::create([
               'client_name'  => $request->client_name,
               'client_phone' => $request->client_phone,
               'booking_date'=> $request->booking_date,
               'notes' => $request->notes,
               'status'  => 'pending',
           ]);
       }
       foreach ($request->services as $serviceId) {
           $service = Service::find($serviceId);

           if (!$service->is_bookable) {
               return ['booking' => null, 'message' => "Service {$service->name} is not bookable"];
           }

           $service->increment('booking_count');
           $booking->services()->attach($serviceId);
       }


       return ['booking' => $booking->load('services'),'message' => 'Booking has been created'];
   }

    public function getAvailableSlots($booking_date, $serviceIds = [])
    {
        $workingHoursStart = Carbon::parse($booking_date . ' 09:00'); // بداية الدوام
        $workingHoursEnd   = Carbon::parse($booking_date . ' 21:00'); // نهاية الدوام


        $totalDuration =(int) Service::whereIn('id', $serviceIds)->sum('duration');

        $bookings = Booking::with('services')
            ->whereDate('booking_date', $booking_date)
            ->get();

        $occupied = [];
        foreach ($bookings as $booking) {
            $start = Carbon::parse($booking->booking_date);
            $end   = $start->copy()->addMinutes((int) $booking->services->sum('duration'));

            foreach ($booking->services as $service) {
                $occupied[] = [
                    'service_id' => $service->id,
                    'start'      => $start,
                    'end'        => $end,
                ];
            }
        }


        $available = [];
        $slotStart = $workingHoursStart->copy();

        while ($slotStart->addMinutes($totalDuration)->lte($workingHoursEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($totalDuration);

            $conflict = false;
            foreach ($occupied as $busy) {

                if (in_array($busy['service_id'], $serviceIds)) {
                    if ($slotStart->lt($busy['end']) && $slotEnd->gt($busy['start'])) {
                        $conflict = true;
                        break;
                    }
                }
            }

            if (!$conflict) {
                $available[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                ];
            }

            $slotStart->addMinutes(15);
        }

        return $available;
    }


}
