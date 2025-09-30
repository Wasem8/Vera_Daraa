<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;

class InvoiceService
{




    public function createInvoice($bookingId)
    {

        $booking = Booking::query()->findOrFail($bookingId);

        if($booking->invoice()->withTrashed()->exists()){
            throw new Exception('Booking already has invoice');
        }

        return Invoice::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'invoice_date'=> now(),
            'total_amount'=> $booking->final_price,
            'paid_amount' => 0,
            'remaining_amount' => $booking->final_price,
        ]);
    }

    public function archive($invoiceId)
    {
        $invoice = Invoice::query()->findOrFail($invoiceId);
        if ($invoice->status !== 'paid') {
            throw new Exception('Invoice is not paid');
        }

        $invoice->delete(); // SoftDelete
        return $invoice;

    }


    public function restore($archiveId){

        $invoice = Invoice::withTrashed()->findOrFail($archiveId);
        $invoice->restore();
        return $invoice;
    }

    public function forceDelete($invoiceId)
    {
        $invoice = Invoice::onlyTrashed()->findOrFail($invoiceId);
        $invoice->forceDelete();
        return true;
    }

}
