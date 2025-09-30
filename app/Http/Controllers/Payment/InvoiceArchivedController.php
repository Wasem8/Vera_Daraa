<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Invoice;
use App\Services\InvoiceService;

class InvoiceArchivedController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService){
        $this->invoiceService = $invoiceService;
    }



    public function index(){
        $archives = Invoice::onlyTrashed()->get();
        return Response::success($archives,'success');
    }

    public function show($id)
    {
        $archive = Invoice::onlyTrashed()->findOrFail($id);
        return Response::success($archive,'success');
    }


    public function archive($invoiceId)
    {
        try {
            $archiver = $this->invoiceService->archive($invoiceId);
            return Response::Success($archiver, 'Invoice archived');
        } catch (\Exception $e) {
            return Response::Error(null, $e->getMessage(), 400);
        }
    }



    public function restoreInvoice($archiveId)
    {

        $invoice = $this->invoiceService->restore($archiveId);
        return Response::Success($invoice,'success');
    }


}
