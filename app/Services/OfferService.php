<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferService
{

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $offer = Offer::create($data);

            $servicesData = [];
            foreach ($data['services'] as $serviceId) {
                $service  = Service::find($serviceId);
                $servicesData[$serviceId] = [
                    'discounted_price' => $service->price * (1 - ($offer->discount_percentage / 100)),
                ];
            }

            $offer->services()->attach($servicesData);

            return Offer::with('services')->find($offer->id);
        });
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $offer = Offer::with('services')->findOrFail($id);
            $offer->update($data);

            if (isset($data['services']) && is_array($data['services'])) {
                $servicesData = [];
                foreach ($data['services'] as $serviceId) {
                    $service  = Service::find($serviceId);
                    $servicesData[$serviceId] = [
                        'discounted_price' => $service->price * (1 - ($offer->discount_percentage / 100)),
                    ];
                }
                $offer->services()->sync($servicesData);
            }

            return $offer->fresh('services');
        });
    }
}
