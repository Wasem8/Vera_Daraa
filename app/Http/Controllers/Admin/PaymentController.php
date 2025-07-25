<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    public function payment($paymentId){
        $payments = Payment::query()->find($paymentId);
        if($payments != null){
            return Response::success($payments,'payment');
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

    public function store(PaymentRequest $request,$bookingId)
    {
        $paymentRequest = $request->validated();
        $booking =  Booking::query()->find($bookingId);
        if (!Auth::user()->hasRole(['admin', 'receptionist'])) {
            return Response::error(false,'you are not authorized');
        }
        if(!$booking){
            return Response::Error(null,'booking not found');
        }
        if ($booking->payment()->exists()) {
            return Response::Success(false,'payment already exist');
        }
        $servicePrice = $booking->service->price;
        if($request->amount != $servicePrice){
            return Response::Success(false,'payment price is not correct');
        }
       $payment = $booking->payment()->create($paymentRequest);

        return Response::Success($payment,'success');
    }

}
