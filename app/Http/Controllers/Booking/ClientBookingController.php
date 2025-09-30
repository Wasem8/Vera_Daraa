<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Services\Booking\BookingService;
use App\Services\Booking\ClientBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientBookingController extends Controller
{
    protected ClientBookingService $clientBookingService;
    protected BookingService $bookingService;

    public function __construct(ClientBookingService $clientBookingService, BookingService $bookingService)
    {
        $this->clientBookingService = $clientBookingService;
        $this->bookingService = $bookingService;
    }

    public function store(Request $request)
    {
        try {
            $data = $this->clientBookingService->book($request);
            if ($data['status'] == 0) {
                return Response::Error($data['data'] , $data['message']);
            }
            return Response::Success($data['data'], $data['message']);
        } catch (\Exception $e) {
            return Response::Error([], $e->getMessage());
        }
    }



    public function update(Request $request, $bookingId)
    {
        try {
            $data = $this->clientBookingService->update($bookingId, $request);
            if ($data['status'] == 0) {
                return Response::Error($data['available_slots'] ?? [], $data['message']);
            }
            return Response::Success($data['data'], $data['message']);
        } catch (\Exception $e) {
            return Response::Error([], $e->getMessage());
        }
    }


    public function destroy($bookingId)
    {
        try {
            $data = $this->clientBookingService->cancel($bookingId);
            if ($data['status'] == 0) {
                return Response::Error($data['data'] ?? [], $data['message']);
            }
            return Response::Success($data['data'], $data['message']);
        } catch (\Exception $e) {
            return Response::Error([], $e->getMessage());
        }
    }

    public function index()
    {

        $bookings = Booking::with(['service', 'offer'])->where('user_id',Auth::id())->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_date' => $booking->booking_date,
                'status' => $booking->status,
                'notes' => $booking->notes,
                'service' => $booking->service,
                'offer' => $booking->offer,
                'final_price' => $booking->final_price,
            ];

        });
        return [
            'status' => 1,
            'data' => $bookings,
            'message' => 'قائمة المواعيد'
        ];
    }

    public function show($id){
        $user_id = Auth::id();
        $booking = Booking::query()->with(['service', 'offer'])->find($id);
        if(!$booking){
            return Response::Error(null,'Booking not found');
        }elseif ($booking->user_id == $user_id || Auth::user()->hasRole(['admin','receptionist']) ){
            return Response::Success([$booking,'final_price'=>$booking->final_price],'success');
        } else {
            return Response::Error(null, 'You are not allowed to book this booking');
        }
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
        ]);

        $slotsData = $this->bookingService->availableSlots($request->service_id, $request->date);

        if ($slotsData['status'] === 1) {
            return Response::Success($slotsData['available_slots'], $slotsData['message']);
        }

        return Response::Error([], $slotsData['message']);
    }



}
