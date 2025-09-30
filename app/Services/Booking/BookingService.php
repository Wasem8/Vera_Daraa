<?php
namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Offer;
use Carbon\Carbon;

class BookingService
{
    public function availableSlots($serviceId, $date)
    {
        $date = Carbon::parse($date);

        if (in_array($date->dayOfWeek, [5, 6])) {
            return [
                'status' => 0,
                'available_slots' => [],
                'message' => 'Booking is not allowed on Friday or Saturday'
            ];
        }

        $service = Service::findOrFail($serviceId);
        $duration = $service->duration ?? 60; // Duration in minutes
        $start = Carbon::parse($date->format('Y-m-d') . ' 09:00');
        $end   = Carbon::parse($date->format('Y-m-d') . ' 21:30');

        $bookings = Booking::where('service_id', $serviceId)
            ->whereDate('booking_date', $date->format('Y-m-d'))
            ->get();

        $availableSlots = [];
        $current = $start->copy();

        while ($current->lt($end)) {
            $slotStart = $current->copy();
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            if ($slotEnd->gt($end)) break;

            $conflict = $bookings->first(function ($booking) use ($slotStart, $slotEnd) {
                $bookingStart = Carbon::parse($booking->booking_date);
                $bookingEnd   = $bookingStart->copy()->addMinutes($booking->service->duration ?? 60);
                return $slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart);
            });

            if (!$conflict) {
                $availableSlots[] = [
                    'start' => $slotStart->format('H:i'),
                    'end'   => $slotEnd->format('H:i'),
                ];
            }

            $current->addMinutes($duration + 15); // duration + 15 mins break
        }

        return [
            'status' => 1,
            'available_slots' => $availableSlots,
            'message' => 'Available slots for the selected day'
        ];
    }


    public function calculatePrice($serviceId, $offerId = null)
    {
        $service = Service::findOrFail($serviceId);
        $price = $service->price;
        $offerIdResult = null;

        if ($offerId) {
            $offer = Offer::with(['services' => fn($q) => $q->where('services.id', $serviceId)])
                ->find($offerId);

            if (!$offer || !$offer->is_active || $offer->start_date > now() || $offer->end_date < now()) {
                return ['status' => 0, 'message' => 'Offer is not valid'];
            }

            $serviceFromOffer = $offer->services->first();
            if (!$serviceFromOffer) {
                return ['status' => 0, 'message' => 'Service is not related to offer'];
            }

            $price = $serviceFromOffer->pivot->discounted_price;
            $offerIdResult = $offer->id;
        }

        return ['status' => 1, 'price' => $price, 'offer_id' => $offerIdResult];
    }
}
