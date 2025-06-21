<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Service;
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
        return Response::Success($data['booking'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function updateBooking(Request $request){
        $data = [];

        try {
            $data = $this->bookingService->updateBooking($request);
            return Response::Success($data['booking'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function getBookings(Request $request)
    {
        $id = Auth::id();
        $bookings = Booking::query()->where('user_id', $id)->get();
        return Response::Success($bookings,'success');
    }

    public function getBooking($id){
        $user_id = Auth::id();
        $booking = Booking::query()->find($id);
        if(!$booking){
            return Response::Error(null,'Booking not found');
        }elseif ($booking->user_id !== $user_id){
            return Response::Error(null,'You are not allowed to book this booking');
        }
        return Response::Success($booking,'success');
    }
}
