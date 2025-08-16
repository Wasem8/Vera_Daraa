<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;

class InvoiceService
{




    public function createInvoice($booking)
    {
        $invoice = Invoice::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'invoice_date'=> now(),
            'total_amount'=> $booking->service->price,
            'paid_amount' => 0,
            'remaining_amount' => $booking->service->price,
        ]);

        return $invoice;

    }
}
