<?php

namespace App\Services;

use App\Http\Responses\Response;
use App\Models\Department;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class  ServiceService
{
    public function create(array $data)
    {
        if(Auth::user()->hasRole(['admin'])){
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = $data['image']->store('services', 'public');
            }else{
                unset($data['image']);
            }
            return Service::query()->create($data);

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
            if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
                if ($service->image && Storage::disk('public')->exists($service->image)) {
                    Storage::disk('public')->delete($service->image);
                }

                $data['image'] = $data['image']->store('services', 'public');
            }else{
                unset($data['image']);
            }

            $service->update($data);

            return $service;
        }else{
            return false;
        }

    }



}
