<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Responses\Response;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    protected ServiceService $service;

    public function __construct(ServiceService $service){
        $this->service = $service;
    }


    public function index(){
        $services = Service::all();
        return Response::Success($services, 'Services List');
    }

    public function store(ServiceRequest $request){
        $serviceRequest = $request->validated();

        try {

            $service = $this->service->create($serviceRequest);
            if($service != false){
                return Response::Success($service, 'Service created successfully');
            }else{
                return Response::Error(false,'you not allowed to create service');
            }

        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }
    }



    public function update(UpdateServiceRequest $request,$serviceId)
    {
        $serviceRequest = $request->validated();

        try {
            $service = Service::query()->find($serviceId);
            if(!$service){
                return Response::Error(false,'service not found');
            }
            $serviceUpdate = $this->service->update($serviceRequest, $service);
            if ($serviceUpdate != false){
                return Response::Success($serviceUpdate, 'Service updated successfully');
            }else{
                return Response::Error(false,'you not allowed to update service');
            }


        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }

    }

    public function destroy($serviceId)
    {
        if(Auth::user()->hasRole(['admin'])){
            $service = Service::query()->find($serviceId);
            if ($service){
                $service->delete();
                return Response::Success(null, 'Service deleted successfully');
            }else{
                return Response::Error([], 'service not found');
            }
        }else{
            return Response::Error(false,"You don't have permission to delete service");
        }




    }



    public function ShowService($id){
        $service = Service::query()->find($id);
        if($service){
            return Response::Success($service,'service:');
        }
        return Response::Error(false,'service not found');
    }



    public function searchServices(Request $request){
        $services = Service::query()
            ->where('name','like','%'.$request->search.'%')
            ->orWhere('description','like','%'.$request->search.'%')->get();
        return Response::Success($services,'services:');
    }
}
