<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class  ServiceService
{
    public function create(array $data)
    {
        if(Auth::user()->hasRole(['admin'])){
            $image = $data['image'] ?? null;
            $service = Service::query()->create($data);
            if (!empty($image)) {
                $path = $image->file('image');
                $name = time() . '.' . $path->getClientOriginalExtension();
                $destinationPath = public_path('/images/services');
                $image->move($destinationPath, $name);
                $service->image = $name;
            }
            return $service;
        }else{
            return false;
        }

    }

    /**
     * @throws \Throwable
     */
    public function update(array $data, Service $service)
    {
        if(Auth::user()->hasRole(['admin'])){
                return DB::transaction(function() use ($service, $data) {
                    $service->update($data);
                    return $service->refresh();
                });

        }else{
            return false;
        }

    }

}
