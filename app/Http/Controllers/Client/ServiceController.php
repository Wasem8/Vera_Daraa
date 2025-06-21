<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function ShowServices()
    {
        $services = Service::all();
        return Response::Success($services,'services:');
    }

    public function ShowService($id){
        $service = Service::query()->find($id);
        return Response::Success($service,'service:');
    }



    public function searchServices(Request $request){
        $services = Service::query()
            ->where('name','like','%'.$request->search.'%')
        ->orWhere('description','like','%'.$request->search.'%')->get();
        return Response::Success($services,'services:');
    }

}
