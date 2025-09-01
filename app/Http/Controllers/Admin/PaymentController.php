<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{


    public function payment($paymentId){
        $payment = Payment::query()->find($paymentId);
        if(!Auth::user()->hasRole(['admin','receptionist'])|| Auth::id() == $payment->user_id){
            return Response::Error(false, "You don't have permission to access this page");
        }
        if($payment != null){
            return Response::success($payment,'payment');
        }
        return Response::success(null,'payment not found');
    }

    public function payments()
    {
        if(Auth::user()->hasRole(['admin', 'receptionist'])){
            $payments = Payment::all();
            return Response::success($payments,'payments');
        }
        return Response::Error(false,'you are not authorized');
    }



    public function store(PaymentRequest $request,$invoiceId)
    {

        if (!Auth::user()->hasRole(['admin', 'receptionist'])) {
            return Response::error(false, 'you are not authorized');
        }
        $invoice = Invoice::query()->find($invoiceId);
        if (!$invoice) {
            return Response::Error(null, 'invoice not found');
        }
        if($invoice->status == 'paid'){
            return Response::Success(false,'already paid');
        }

        if($request->amount > $invoice->remaining_amount){
            return Response::Error(false,"the amount {$request->amount} bigger than remaining amount: {$invoice->remaining_amount}");
        }

        $payment = Payment::create([
            'amount' => $request->amount,
            'payment_date' => now(),
            'invoice_id' => $invoiceId
        ]);

        $invoice->updateAmounts();
        return Response::Success($invoice,'success');
    }

}
