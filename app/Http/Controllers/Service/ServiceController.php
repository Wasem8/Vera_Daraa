<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Responses\Response;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected ServiceService $service;

    public function __construct(ServiceService $service){
        $this->service = $service;
    }


    public function index(){
        $services = Service::query()->with('offers')->paginate(10);
        return Response::Success($services, 'Services List');
    }

    public function Show($id){
        $service = Service::query()->with('offers')->findOrFail($id);
            return Response::Success($service,'service:');
    }

    public function store(ServiceRequest $request){

        try {
        $serviceRequest = $request->validated();
        $service = $this->service->create($serviceRequest);
        return Response::Success($service, 'Service created successfully');
          }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }
    }



    public function update(UpdateServiceRequest $request,$serviceId)
    {
        $serviceRequest = $request->validated();

        try {
            $service = Service::query()->findOrFail($serviceId);
            $serviceUpdate = $this->service->update($serviceRequest, $service);
            return Response::Success($serviceUpdate, 'Service updated successfully');
        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }

    }

    public function destroy($serviceId)
    {
        $data = $this->service->delete($serviceId);
        return Response::Success(null, 'Service deleted successfully');
    }


    public function searchServices(Request $request){
        $services = Service::query()
            ->when($request->search, function ($query, $search){
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })->paginate(10);
        return Response::Success($services,'services:');
    }
}
