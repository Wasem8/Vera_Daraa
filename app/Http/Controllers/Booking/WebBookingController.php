<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\BookingArchiver;
use App\Notifications\BookingStatusChanged;
use App\Services\Booking\BookingArchivedService;
use App\Services\Booking\BookingService;
use App\Services\Booking\WebBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//use Illuminate\Support\Facades\Auth;

class WebBookingController extends Controller
{
    protected BookingService $bookingService;
    protected BookingArchivedService $bookingArchivedService;
    protected WebBookingService $webBookingService;


    public function __construct(BookingService $bookingService,WebBookingService $webBookingService, BookingArchivedService $bookingArchivedService){
        $this->bookingService = $bookingService;
        $this->bookingArchivedService = $bookingArchivedService;
        $this->webBookingService = $webBookingService;
    }


    public function store(BookingRequest $request){

            try {
                $data = $this->webBookingService->bookForClient($request);
                if ($data['status'] == 0) {
                    return Response::Error($data['data'] , $data['message']);
                }
                return Response::Success($data['data'], $data['message']);
            } catch (\Exception $e) {
                return Response::Error([], $e->getMessage());
            }

    }


    public function update(BookingRequest $request ,$bookingId){

        try {
            $data = $this->webBookingService->update($bookingId, $request);
            if ($data['status'] == 0) {
                return Response::Error($data['available_slots'] ?? [], $data['message']);
            }
            return Response::Success($data['data'], $data['message']);
        } catch (\Exception $e) {
            return Response::Error([], $e->getMessage());
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


    public function destroy($bookingId)
    {
        try {
            $data = $this->webBookingService->cancel($bookingId);
            return Response::Success($data['data'], $data['message']);
        } catch (\Exception $e) {
            return Response::Error([], $e->getMessage());
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
        $bookings = Booking::query()->with(['service','user'])->get();
        return Response::Success($bookings,'success');

    }

    public function show($id)
    {
            $booking = Booking::query()->with(['service','user'])->findOrFail($id);
            return Response::Success($booking,'success');

    }
}
