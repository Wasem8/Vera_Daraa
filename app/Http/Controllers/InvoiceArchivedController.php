<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Invoice;
use App\Models\InvoiceArchiver;
use App\Services\InvoiceArchivedService;
use Illuminate\Http\Request;

class InvoiceArchivedController extends Controller
{
    protected InvoiceArchivedService $invoiceArchivedService;

    public function __construct(InvoiceArchivedService $invoiceArchivedService){
        $this->invoiceArchivedService = $invoiceArchivedService;
    }



    public function index(){
        $archives = InvoiceArchiver::all();
        return Response::success($archives,'success');
    }

    public function show($id)
    {
        $archive = InvoiceArchiver::query()->find($id);
        if(!$archive){
            return Response::Error(null,'invoice not found');
        }
        return Response::success($archive,'success');
    }


    public function archive($invoiceId)
    {
        $invoice = Invoice::query()->find($invoiceId);
        if (!$invoice) {
            return Response::Error(null,'Invoice not found');
        }
        if($invoice->status !== 'paid'){
            return Response::Error(null,'Invoice is not paid');
        }

        $archiver = $this->invoiceArchivedService->archive($invoice);
        return Response::Success($archiver,'success');
    }


    public function restoreInvoice($archiveId)
    {
        $archive = InvoiceArchiver::query()->find($archiveId);
        if (!$archive) {
            return Response::Error(null,'Invoice not found');
        }

        $invoice = $this->invoiceArchivedService->restore($archive);
        return Response::Success($invoice,'success');
    }
}
