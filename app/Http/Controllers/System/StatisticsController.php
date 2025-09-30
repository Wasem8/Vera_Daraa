<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Services\StatisticServices;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected StatisticServices $statisticService;

    public function __construct(StatisticServices $statisticServices){
        $this->statisticService = $statisticServices;
    }

    public function numberClients(Request $request)
    {

       $data = $this->statisticService->numberClients($request);
       return Response::success($data,'number Clients');
    }


    public function mostPopularServices()
    {
        $data = $this->statisticService->mostPopularServices();
        return Response::Success($data,'most popular services');
    }

    public function financialReports(Request $request)
    {
        try {
            $reportType = $request->input('report_type', 'monthly');
            $date = $request->input('date', now()->format('Y-m'));

            $report = $this->statisticService->financialReports($reportType, $date);

            return Response::Success($report, 'success');
        } catch (\Exception $e) {
            return Response::Error(null, $e->getMessage(), 400);
        }
    }

}
