<?php

namespace App\Http\Controllers;

use App\Http\Responses\Response;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    public function numberClients(Request $request)
    {

        if(!Auth::user()->hasRole(['admin'])){
            return Response::Error(false,'you dont have permission to show numberClients');
        }
        $startDate = $request->input('start_date',Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date',Carbon::now()->format('Y-m-d'));

        $newClient = User::role('client')
            ->whereBetween('created_at',[$startDate,$endDate])->count();
        return Response::Success($newClient,'number of clients');
    }


    public function mostPopularServices()
    {

        if(!Auth::user()->hasRole(['admin'])){
            return Response::Error(false,'you dont have permission to show mostPopularServices');
        }
        $services = Service::withCount('bookings')
            ->orderBy('booking_count','desc')
            ->take(10)
            ->get();

        return Response::Success($services,'most popular services');
    }
}
