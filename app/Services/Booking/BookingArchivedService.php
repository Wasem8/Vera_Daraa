<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\BookingArchiver;

class BookingArchivedService
{

    public function archive($booking)
    {
        $bookingArchive = BookingArchiver::query()->create([
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
            'service_id' => $booking->service_id,
            'status' => $booking->status,
            'note' => $booking->note,
            'archived_at' => now(),
            'booking_date'=> $booking->booking_date,
        ]);
        $booking->delete();
        return $bookingArchive;
    }

    public function unArchive($archive)
    {
        $booking = Booking::query()->create([
            'booking_id' => $archive->id,
            'user_id' => $archive->user_id,
            'service_id' => $archive->service_id,
            'status' => $archive->status,
            'note' => $archive->note,
            'booking_date'=> $archive->booking_date,
        ]);
        $archive->delete();
        return $booking;
    }
}
