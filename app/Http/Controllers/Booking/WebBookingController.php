<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\BookingArchiver;
use App\Models\Service;
use App\Services\BookingArchivedService;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

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


    public function bookingApprove($bookingId)
    {
        $booking = Booking::query()->find($bookingId);
        if (!$booking) {
                return Response::Error(null,"Booking not found");
        }
        if ($booking->status == 'confirmed') {
            return Response::Error(null,"Booking already confirmed");
        }
        $booking->update(['status' => 'confirmed']);
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


        public function availableSlots(Request $request)
    {
        $request->validate([
            'booking_date' => 'required|date',
            'services' => 'required|array|min:1'
        ]);

        $slots = app(BookingService::class)->getAvailableSlots(
            $request->booking_date,
            $request->services
        );

        return response()->json([
            'booking_date' => $request->booking_date,
            'services' => $request->services,
            'available_slots' => $slots
        ]);
    }


}
