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
        $total = $booking->services->sum(function ($service) {
            return $service->pivot->price ?? $service->price;
        });

        $invoice = Invoice::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'invoice_date'=> now(),
            'total_amount'=> $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
        ]);

        foreach ($booking->services as $service) {
            $invoice->items()->create([
                'service_id' => $service->id,
                'name'       => $service->name,
                'price'      => $service->pivot->price ?? $service->price,
            ]);
        }

        return $invoice->load('items.service');




    }
}
