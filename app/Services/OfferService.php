<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class OfferService
{

    public function store(array $data)
    {
            if(Auth::user()->hasRole(['admin'])){
                $offer = Offer::query()->create($data);
                foreach ($data['services'] as $serviceId) {
                    $service  = Service::query()->find($serviceId);
                    $discountedPrice = $service->price * (1 - ($offer->discount_percentage / 100));
                    $offer->services()->attach($serviceId,[
                        'discounted_price' => $discountedPrice,
                    ]);
                }
                $offer = Offer::with('services')->find($offer->id);
                return $offer;
            }else{
                return false;
            }
    }

    public function update(array $data, $id){
        if(Auth::user()->hasRole(['admin'])) {
            $offer = Offer::with('services')->find($id);
            $offer->update($data);
            if(isset($data['services']) && is_array($data['services'])){
                $servicesData = [];
                foreach ($data['services'] as $serviceId) {
                    $service  = Service::query()->find($serviceId);
                    $discountedPrice = $service->price * (1 - ($offer->discount_percentage / 100));
                    $servicesData[$serviceId] = [
                        'discounted_price' => $discountedPrice,
                    ];
                }
                $offer->services()->sync($servicesData);
            }

            return $offer;

        }else{
            return false;
        }
    }
}
