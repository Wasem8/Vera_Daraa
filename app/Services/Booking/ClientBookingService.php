<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientBookingService
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService){
        $this->bookingService = $bookingService;
    }

    public function book($request)
    {
        $slots = $this->bookingService->availableSlots($request->service_id, $request->booking_date);

        if ($slots['status'] == 0 || empty($slots['available_slots'])) {
            return [
                'status'=>0,
                'message'=>$slots['message'],
                'data'=>$slots['available_slots']??[],

            ];
        }

        $requestedTime = Carbon::parse($request->booking_date)->format('H:i');
        $found = collect($slots['available_slots'])->first(fn($slot) => $slot['start'] <= $requestedTime && $slot['end'] > $requestedTime);

        if (!$found) {
            return [
                'status'=>0,
                'message'=>'Selected time is not available',
                'data'=>$slots['available_slots'],

            ];
        }

        $priceData = $this->bookingService->calculatePrice($request->service_id, $request->offer_id);
        if ($priceData['status'] == 0) {
            return ['status'=>0,'message'=>$priceData['message'],'data'=>[]];
        }

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'service_id' => $request->service_id,
            'booking_date' => $request->booking_date,
            'offer_id' => $priceData['offer_id'],
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        $booking->service()->increment('booking_count');

        return [
            'status'=>1,
            'data'=>[
                'booking'=>$booking,
                'final_price'=>$priceData['price']
            ],
            'message'=>'Booking has been created'
        ];
    }

    public function update($bookingId, $request)
    {
        $booking = Booking::find($bookingId);
        if (!$booking) return ['status'=>0,'message'=>'Booking not found','data'=>[]];
        if ($booking->user_id != Auth::id()) return ['status'=>0,'message'=>'You are not allowed to update this booking','data'=>[]];

        $slots = $this->bookingService->availableSlots($request->service_id, $request->booking_date);
        if ($slots['status'] == 0 || empty($slots['available_slots'])) {
            return [
                'status'=>0,
                'message'=>$slots['message'],
                'data'=>$slots['available_slots']??[],
            ];
        }

        $requestedTime = Carbon::parse($request->booking_date)->format('H:i');
        $found = collect($slots['available_slots'])->first(fn($slot) => $slot['start'] <= $requestedTime && $slot['end'] > $requestedTime);

        if (!$found) {
            return [
                'status'=>0,
                'message'=>'Selected time is not available',
                'data'=>$slots['available_slots'],
            ];
        }

        $priceData = $this->bookingService->calculatePrice($request->service_id, $request->offer_id);
        if ($priceData['status'] == 0) {
            return ['status'=>0,'message'=>$priceData['message'],'data'=>[]];
        }

        $booking->update([
            'service_id' => $request->service_id,
            'booking_date' => $request->booking_date,
            'offer_id' => $priceData['offer_id'],
            'notes' => $request->notes,
        ]);

        return [
            'status'=>1,
            'data'=>[
                'booking'=>$booking->load('service','offer'),
                'final_price'=>$priceData['price']
            ],
            'message'=>'Booking has been updated'
        ];
    }

    public function cancel($bookingId)
    {
        $booking = Booking::find($bookingId);
        if (!$booking) {
            return ['status'=>0,'message'=>'Booking not found','data'=>[]];
        }


        if ($booking->user_id != Auth::id() && !Auth::user()->hasRole(['admin','receptionist'])) {
            return ['status'=>0,'message'=>'You are not allowed to cancel this booking','data'=>[]];
        }


        if (Carbon::parse($booking->booking_date)->isPast()) {
            return ['status'=>0,'message'=>'Cannot cancel a past booking','data'=>[]];
        }


        if (Carbon::now()->diffInMinutes(Carbon::parse($booking->booking_date), false) < 120) {
            return ['status'=>0,'message'=>'Cannot cancel within 2 hours of the appointment','data'=>[]];
        }


        if (in_array($booking->status, ['cancelled','confirmed','rejected'])) {
            return ['status'=>0,'message'=>'Booking cannot be cancelled','data'=>[]];
        }


        $booking->update(['status'=>'cancelled']);
        $booking->service()->decrement('booking_count');

        return ['status'=>1,'data'=>['booking'=>$booking],'message'=>'Booking has been cancelled'];
    }

}
