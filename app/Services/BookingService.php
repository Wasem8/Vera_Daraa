<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Offer;
use App\Models\Service;
use App\Notifications\BookingStatusChanged;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function booking($request)
    {
        $service = Service::query()->find($request->service_id);
        if (!$service) {
            return ['status' => 0, 'data' => [], 'message' => 'service not found'];
        }
        $price = $service->price;
        $offerId = null;


        if (!empty($request->offer_id)) {
            $offer = Offer::with(['services' => function ($q) use ($service) {
                $q->where('services.id', $service->id);
            }])->find($request->offer_id);

            if (!$offer || !$offer->is_active || $offer->start_date > now() || $offer->end_date < now()) {
                return ['status' => 0, 'data' => [], 'message' => 'the offer is not valid'];
            }

            $serviceFromOffer = $offer->services->first();
            if (!$serviceFromOffer) {
                return ['status' => 0, 'data' => [], 'message' => 'the service is not related to offer'];
            }

            $price = $serviceFromOffer->pivot->discounted_price;
            $offerId = $offer->id;
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'offer_id' => 'nullable|exists:offers,id',
            'notes' => 'string',
        ]);

        $slots = $this->availableSlots($request->service_id, $request->booking_date);

        if ($slots['status'] == 0 || empty($slots['available_slots'])) {
            return [
                'status' => 0,
                'message' => $slots['message'] ?? 'الوقت غير متاح'
            ];
        }


        $requestedTime = Carbon::parse($request->booking_date)->format('H:i');
        $found = collect($slots['available_slots'])->first(function ($slot) use ($requestedTime) {
            return $slot['start'] <= $requestedTime && $slot['end'] > $requestedTime;
        });

        if (!$found) {
            return [
                'status' => 0,
                'message' => 'time is not available , please try another time',
                'available_slots' => $slots['available_slots']
            ];
        }


        $booking = Booking::create([
            'user_id' => Auth::id(),
            'booking_date' => $request->booking_date,
            'offer_id' => $offerId,
            'notes' => $request->notes,
            'status' => 'pending',
            'service_id' => $request->service_id,
        ]);

        $service = Service::find($request->service_id);

        if (!$service->is_bookable) {
            return ['booking' => null, 'message' => "Service {$service->name} is not bookable"];

        }
        $service->increment('booking_count');
        if ($booking->user) {
            $message = "Your booking for service{$service->name} has been created to {$booking->booking_date} please wait to confirm your booking.";
            $booking->user->notify(new BookingStatusChanged($booking, 'pending', $message,'Create Booking'));
        }


        return [  'data' => [
                'booking' => $booking->load(['service', 'offer']),
                'final_price' => $booking->final_price,
            ], 'message' => 'Booking has been created','status'=>1];

        }

        public function updateBooking($request,$bookingId)
        {
            $booking = Booking::find($bookingId);
            if (!$booking) {
                return [
                    'status' => 0,
                    'message' => 'Booking not found'
                ];
            }
            $service = Service::query()->find($request->service_id);
            if(!$service){
                return ['status' => 0, 'data' => [], 'message' => 'the service is not found'];
            }
            $price = $service->price;
            $offerId = null;

            if (!empty($request->offer_id)) {
                $offer = Offer::with(['services' => function ($q) use ($service) {
                    $q->where('services.id', $service->id);
                }])->find($request->offer_id);

                if (!$offer || !$offer->is_active || $offer->start_date > now() || $offer->end_date < now()) {
                    return ['status' => 0, 'data' => [], 'message' => 'offer is not valid'];
                }

                $serviceFromOffer = $offer->services->first();
                if (!$serviceFromOffer) {
                    return ['status' => 0, 'data' => [], 'message' => 'the service is not related to offer'];
                }

                $price = $serviceFromOffer->pivot->discounted_price;
                $offerId = $offer->id;
            }


            $request->validate([
                'service_id' => 'exists:services,id',
                'booking_date' => 'date',
                'offer_id'=>'nullable|exists:offers,id',
                'notes' => 'string',
            ]);

            $slots = $this->availableSlots($request->service_id, $request->booking_date);

            if ($slots['status'] == 0 || empty($slots['available_slots'])) {
                return [
                    'status' => 0,
                    'message' => $slots['message'] ?? 'الوقت غير متاح'
                ];
            }


            $requestedTime = Carbon::parse($request->booking_date)->format('H:i');
            $found = collect($slots['available_slots'])->first(function ($slot) use ($requestedTime) {
                return $slot['start'] <= $requestedTime && $slot['end'] > $requestedTime;
            });

            if (!$found) {
                return [
                    'status' => 0,
                    'message' => 'time is not available , please try another time',
                    'available_slots' => $slots['available_slots']
                ];
            }

            $booking->update([
                'booking_date' => $request->booking_date,
                'service_id'   => $request->service_id,
                'offer_id' => $request->offer_id,
                'notes'        => $request->notes,
            ]);


            return [
                'status' => 1,
                'data' => [
                    'booking' => $booking->load(['service', 'offer']),
                    'final_price' => $booking->final_price,
                ],
                'message' => 'booking update successfully'
            ];
        }



    public function deleteBooking($bookingId)
        {
            $booking = Booking::query()->find($bookingId);

            if (!$booking) {
                return ['booking' => null, 'message' => 'Booking not found'];
            } elseif ($booking->status == 'cancelled') {
                return ['booking' => null, 'message' => 'Booking has been cancelled'];
            } elseif (Auth::user()->hasRole('client') && Auth::id() == $booking->user_id || Auth::user()->hasRole(['admin', 'receptionist'])) {
                $booking->update([
                    'status' => 'cancelled'
                ]);
                $booking->service()->decrement('booking_count');
                return ['booking' => $booking, 'message' => 'Booking has been cancelled'];
            } else {
                return ['booking' => null, 'message' => 'You are not allowed to delete booking'];
            }
        }

        //booking by receptionist
        public
        function storeBooking($request): array
        {
            $request->validate([
                'service_id' => 'exists:services,id',
                'booking_date' => 'required|date',
                'notes' => 'string',
                'user_id' => 'required|exists:users,id',
            ]);


            $slots = $this->availableSlots($request->service_id, $request->booking_date);

            if ($slots['status'] == 0 || empty($slots['available_slots'])) {
                return [
                    'status' => 0,
                    'message' => $slots['message'] ?? 'الوقت غير متاح'
                ];
            }


            $requestedTime = Carbon::parse($request->booking_date)->format('H:i');
            $found = collect($slots['available_slots'])->first(function ($slot) use ($requestedTime) {
                return $slot['start'] <= $requestedTime && $slot['end'] > $requestedTime;
            });

            if (!$found) {
                return [
                    'status' => 0,
                    'message' => 'الوقت غير متاح، الرجاء اختيار وقت آخر.',
                    'available_slots' => $slots['available_slots']
                ];
            }
                $booking = Booking::create([
                    'user_id' => $request->user_id,
                    'service_id' => $request->service_id,
                    'booking_date' => $request->booking_date,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);


                $service = Service::find($request->service_id);

                if (!$service->is_bookable) {
                    return ['booking' => null, 'message' => "Service {$service->name} is not bookable"];
            }

            $booking->service()->increment('booking_count');
            return ['booking' => $booking->load('service'), 'message' => 'Booking has been created','status'=>1];
        }


        public
        function availableSlots($serviceId, $date)
        {
            $date = Carbon::parse($date);


            if (in_array($date->dayOfWeek, [5, 6])) {
                return [
                    'status' => 0,
                    'available_slots' => [],
                    'message' => 'لا يمكن الحجز يوم الجمعة أو السبت.'
                ];
            }

            $service = Service::findOrFail($serviceId);
            $duration = $service->duration ?? 60;

            $start = Carbon::parse($date->format('Y-m-d') . ' 09:00');
            $end = Carbon::parse($date->format('Y-m-d') . ' 21:30');

            $bookings = Booking::where('service_id', $serviceId)
                ->whereDate('booking_date', $date->format('Y-m-d'))
                ->get();

            $availableSlots = [];
            $current = $start;

            while ($current->addMinutes(0) < $end) {
                $slotStart = $current->copy();
                $slotEnd = $current->copy()->addMinutes($duration);

                if ($slotEnd > $end) {
                    break;
                }


                $conflict = $bookings->first(function ($booking) use ($slotStart, $slotEnd) {
                    $bookingStart = Carbon::parse($booking->booking_date);
                    $bookingEnd = $bookingStart->copy()->addMinutes(60);

                    return $slotStart->between($bookingStart, $bookingEnd) ||
                        $slotEnd->between($bookingStart, $bookingEnd) ||
                        ($slotStart <= $bookingStart && $slotEnd >= $bookingEnd);
                });

                if (!$conflict) {
                    $availableSlots[] = [
                        'start' => $slotStart->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                    ];
                }

                $current->addMinutes($duration + 15);
            }

            return [
                'status' => 1,
                'available_slots' => $availableSlots,
                'message' => 'الأوقات المتاحة لليوم المحدد'
            ];
        }
    }
