<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
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
            $invoices = Invoice::all();
            return Response::Success($invoices->load(['booking.service','payments']), 'success');
    }


    public function show($id)
    {
            $invoice = Invoice::with(['booking.service','payments'])->findOrFail($id);
                return Response::Success($invoice, 'success');
    }



    public function store($bookingId)
    {
        $invoice = $this->invoiceService->createInvoice($bookingId);
        return Response::Success($invoice,'success');
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

    public function destroy($invoiceId)
    {
        $this->invoiceService->forceDelete($invoiceId);
        return Response::Success([], 'Invoice permanently deleted');
    }

}
