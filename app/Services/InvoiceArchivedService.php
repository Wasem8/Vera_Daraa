<?php

namespace App\Services;
use App\Models\Invoice;
use App\Models\InvoiceArchiver;

class InvoiceArchivedService
{
        public function archive($invoice)
        {
            $invoiceArchiver = InvoiceArchiver::query()->create([
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'booking_id' => $invoice->booking_id,
                'total_amount' => $invoice->total_amount,
                'paid_amount' => $invoice->paid_amount,
                'remaining_amount' => $invoice->remaining_amount,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date,
            ]);
            $invoice->payments()->delete();
            $invoice->delete();
            return $invoiceArchiver;
        }


        public function restore($archiveInvoice){
            $invoice = Invoice::query()->create([
                'invoice_id' => $archiveInvoice->id,
                'user_id' => $archiveInvoice->user_id,
                'booking_id' => $archiveInvoice->booking_id,
                'total_amount' => $archiveInvoice->total_amount,
                'paid_amount' => $archiveInvoice->paid_amount,
                'remaining_amount' => $archiveInvoice->remaining_amount,
                'status' => $archiveInvoice->status,
                'invoice_date' => $archiveInvoice->invoice_date,
            ]);


            $archiveInvoice->delete();

            return $invoice ;
        }
}
