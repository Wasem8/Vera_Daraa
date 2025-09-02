<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    private $bookingService;

    public function __construct(BookingService $bookingService){
        $this->bookingService = $bookingService;
    }
    public function booking(Request $request){
        $data = [];

        try {

        $data = $this->bookingService->booking($request);
        if ($data['status'] == 0) {
            return response()->json([
                'status' => 0,
                'message' => $data['message'],
                'available_slots' => $data['available_slots'] ?? []
            ], 400);
        }
        return Response::Success($data['data'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function updateBooking(Request $request ,$bookingId){
        $data = [];

        try {
            $data = $this->bookingService->updateBooking($request,$bookingId);
            if ($data['status'] == 0) {
                return response()->json([
                    'status' => 0,
                    'message' => $data['message'],
                    'available_slots' => $data['available_slots'] ?? []
                ], 400);
            }
            return Response::Success($data['data'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function deleteBooking($bookingId)
    {
        $data = [];
        try {
            $data = $this->bookingService->deleteBooking($bookingId);
            return Response::Success($data['booking'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function getBookings()
    {

        $bookings = Booking::with(['service', 'offer'])->where('user_id',Auth::id())->get()->map(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_date' => $booking->booking_date,
                'status' => $booking->status,
                'notes' => $booking->notes,
                'service' => $booking->service,
                'offer' => $booking->offer,
                'final_price' => $booking->final_price, // ✅ السعر بعد الخصم
            ];

        });
        return [
            'status' => 1,
            'data' => $bookings,
            'message' => 'قائمة المواعيد'
        ];
    }

    public function getBooking($id){
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

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date' => 'required|date',
        ]);

        $slots = $this->bookingService->availableSlots(
            $request->service_id,
            $request->date
        );

        return response()->json([
            'status' => 1,
            'data' => $slots
        ]);
    }


}
