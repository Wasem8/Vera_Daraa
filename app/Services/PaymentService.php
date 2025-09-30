<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Notifications\BookingStatusChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createPayment(Invoice $invoice, float $amount): Payment
    {
        return DB::transaction(function () use ($invoice, $amount) {

            if ($invoice->status === 'paid') {
                throw new \Exception('Invoice already paid.');
            }

            if ($amount > $invoice->remaining_amount) {
                throw new \Exception("The amount {$amount} is bigger than remaining amount: {$invoice->remaining_amount}");
            }


            $payment = Payment::create([
                'amount' => $amount,
                'payment_date' => now(),
                'invoice_id' => $invoice->id,
                'user_id' => Auth::id(),
            ]);

            $invoice->updateAmounts();


            $user = $invoice->user;
            if ($user) {
                $message = "Your payment of {$payment->amount} has been received for invoice #{$invoice->id}. Remaining amount: {$invoice->remaining_amount}.";
                $user->notify(new BookingStatusChanged(
                    $invoice->booking ?? null,
                    'payment',
                    $message,
                    'Payment Received'
                ));
            }

            return $payment;
        });
    }
}
