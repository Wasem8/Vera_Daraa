<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Responses\Response;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;

class PaymentController extends Controller
{


    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function payment($paymentId){
        $payment = Payment::query()->findOrFail($paymentId);
        return Response::success($payment,'payment');

    }

    public function payments()
    {
            $payments = Payment::all();
            return Response::success($payments,'payments');
    }



    public function store(PaymentRequest $request, $invoiceId)
    {

        $invoice = Invoice::query()->findOrFail($invoiceId);
        try {
            $payment = $this->paymentService->createPayment($invoice, $request->amount);
            return Response::Success($payment, 'Payment successful');
        } catch (\Exception $e) {
            return Response::Error(null, $e->getMessage());
        }
    }

}
