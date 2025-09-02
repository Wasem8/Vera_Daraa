<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceArchiver;
use App\Services\InvoiceArchivedService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService){
        $this->invoiceService = $invoiceService;
    }



    public function index()
    {
        if(Auth::user()->hasRole(['admin','receptionist'])) {
            $invoices = Invoice::all();
            return Response::Success($invoices->load(['booking.service','payments']), 'success');
        }
        return Response::Error(401, 'Unauthorized');
    }


    public function show($id)
    {
        if (Auth::user()->hasRole(['admin','receptionist'])) {
            $invoice = Invoice::with(['booking.service','payments'])->find($id);
            if(!$invoice){
                return Response::Error(404, 'Invoice not found');
            }else{
                return Response::Success($invoice, 'success');
            }
        }else{
            return Response::Error(401, 'Unauthorized');
        }

    }



    public function store($bookingId)
    {
        $booking = Booking::query()->find($bookingId);
        if (!$booking) {
            return Response::Error(null,'Booking not found');
        }
        if($booking->invoice()->exists()){
            return Response::Error(null,'Booking already has invoice');
        }
        $invoice = $this->invoiceService->createInvoice($booking);
        return Response::Success($invoice,'success');
    }


    public function financialReports(Request $request)
    {
        $reportType = $request->input('report_type','monthly');
        $date = $request->input('date',now()->format('Y-m'));

        if($reportType == 'monthly'){
            $startDate = Carbon::parse($date)->startOfMonth();
            $endDate = Carbon::parse($date)->endOfMonth();
            $title = "monthly report:". $startDate->year;
        }else{
            $startDate = Carbon::parse($date)->startOfYear();
            $endDate = Carbon::parse($date)->endOfYear();
            $title = "yearly report:". $startDate->year;
        }
        $invoices =  Invoice::query()->whereBetween('invoice_date', [$startDate, $endDate])
        ->orderBy('invoice_date')->get();

        $summary = [
            'total_invoices'=> $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'paid_amount' => $invoices->sum('paid_amount'),
            'remaining_amount' => $invoices->sum('remaining_amount'),
        ];

        return Response::Success([$title,$summary,$invoices,$date], 'success');
    }


    public function clientInvoices()
    {
        $user_id = Auth::id();
        $invoices = Invoice::with(['booking.service','payments'])->where('user_id',$user_id)->get();
        if(Auth::user()->hasRole('client')) {
            return Response::Success($invoices, 'success');
        }
        return Response::Error(401, 'Unauthorized');
    }

    public function clientInvoice($id)
    {
        $user_id = Auth::id();
        $invoice = Invoice::with(['booking.service','payments'])->where('user_id',$user_id)->find($id);
        if(!$invoice){
            return Response::Error(404, 'Invoice not found');
        }
            return Response::Success($invoice, 'success');

    }



}
