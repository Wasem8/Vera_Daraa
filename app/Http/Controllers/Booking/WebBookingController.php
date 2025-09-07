<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\BookingArchiver;
//use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Notifications\BookingStatusChanged;
use App\Services\BookingArchivedService;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebBookingController extends Controller
{
    protected BookingService $bookingService;
    protected BookingArchivedService $bookingArchivedService;


    public function __construct(BookingService $bookingService, BookingArchivedService $bookingArchivedService){
        $this->bookingService = $bookingService;
        $this->bookingArchivedService = $bookingArchivedService;
    }


    public function storeBooking(Request $request){
        $data = [];

        try {
            $data = $this->bookingService->storeBooking($request);
            if ($data['status'] == 0) {
                return response()->json([
                    'status' => 0,
                    'message' => $data['message'],
                    'available_slots' => $data['available_slots'] ?? []
                ], 400);
            }
                return Response::Success($data['booking'],$data['message']);
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


    public function bookingApprove($bookingId)
    {
        $booking = Booking::query()->with('user')->find($bookingId);
        if (!$booking) {
                return Response::Error(null,"Booking not found");
        }
        if ($booking->status == 'confirmed') {
            return Response::Error(null,"Booking already confirmed");
        }
        $booking->update(['status' => 'confirmed']);
        if ($booking->user) {
            $booking->user->notify(
                new BookingStatusChanged($booking, 'confirmed', 'Your booking has been approved.','Approved Booking'));
        }
        return Response::Success($booking,'booking approved successfully');
    }



    public function bookingReject($bookingId){
        $booking = Booking::query()->find($bookingId);
        if (!$booking) {
            return Response::Error(null,"Booking not found");
        }
        if ($booking->status == 'rejected') {
            return Response::Error(null,"Booking already rejected");
        }
        $booking->update(['status' => 'rejected']);
        if ($booking->user) {
            $booking->user->notify(
                new BookingStatusChanged($booking, 'rejected', 'Your booking has been rejected.','Rejected Booking'));
        }
        return Response::Success($booking,'booking rejected successfully');
    }


    public function dailyBooking($data = null)
    {
        $data = $data ? Carbon::parse($data) : Carbon::today();

        $bookings = Booking::with('user')
            ->whereDate('booking_date', $data)
            ->get();
        return Response::Success($bookings,'success');
    }


    public function canceledBooking($bookingId)
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

    public function archive($bookingId)
    {
        $booking = Booking::query()->find($bookingId);
        if (!$booking) {
            return Response::Error(null,"Booking not found");
        }

        $archiver = $this->bookingArchivedService->archive($booking);
        return Response::Success($archiver,'booking archived successfully');
    }



    public function unArchive($archiveId)
    {
        $archive = BookingArchiver::query()->find($archiveId);
        if (!$archive) {
            return Response::Error(null,'archive not found');
        }

        $invoice = $this->bookingArchivedService->unArchive($archive);
        return Response::Success($invoice,'success');
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


    public function index()
    {
        if(Auth::user()->hasRole(['admin','receptionist'])){
        $bookings = Booking::query()->with(['service','user'])->get();
        return Response::Success($bookings,'success');
            }
        return Response::Error(false,'you dont have permissions');
    }

    public function show($id)
    {
        if(Auth::user()->hasRole(['admin','receptionist'])){
            $booking = Booking::query()->with(['service','user'])->find($id);
            return Response::Success($booking,'success');
        }
        return Response::Error(false,'you dont have permissions');
    }
}
