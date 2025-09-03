<?php

namespace App\Http\Controllers;

use App\Http\Requests\OfferRequest;
use App\Http\Requests\UpdateOfferRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Responses\Response;
use App\Models\Offer;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    protected OfferService $offerService;
    public function __construct(OfferService $offerService){
        $this->offerService = $offerService;
    }

    public function index(Request $request){
        $validated = $request->validate([
            'status' => 'required|string|in:all,active,inactive,expired',
        ]);
        if($request->status == 'all'){
            $offers = Offer::query()->with('services')->get();
            return Response::Success($offers, 'offers retrieved successfully');
        }elseif ($request->status == 'active') {
            $offers = Offer::query()->where('is_active', 1)->get();
            return Response::Success($offers, 'offers retrieved successfully');
        }elseif ($request->status == 'inactive') {
            $offers = Offer::query()->where('is_active', 0)->get();
            return Response::Success($offers, 'offers retrieved successfully');
        }elseif ($request->status == 'expired') {
            $now = now();
            $offers = Offer::query()->where('end_date','>',$now)->get();
            return Response::Success($offers, 'offers retrieved successfully');
        }

    }

    public function show($id)
    {
        $offer = Offer::with('services')->find($id);
        if(!$offer){
            return Response::Error(null,"offer not found");
        }
        if($offer->is_active == 1){
                return Response::Success($offer, 'offer retrieved successfully');


        }else{
            return Response::Error(null,"offer is not active");
        }

    }

    public function store(OfferRequest $request)
    {
        $offerRequest = $request->validated();

        try {

            if(!Auth::user()->hasRole(['admin'])){
                return Response::Error(false,'you dont have permission to create offer');
            }
        $offer = $this->offerService->store($offerRequest);
        if($offer != false){
            return Response::Success($offer, 'offer created successfully');
        }else{
            return Response::Error(false,'you not allowed to create offer');
        }

        }catch (\Exception $exception){
            return Response::Error($exception->getMessage(), $exception->getCode());
        }
   }


   public function update(UpdateOfferRequest $request,$id)
   {
       if(!Auth::user()->hasRole(['admin'])){
           return Response::Error(false,'you dont have permission to update offer');
       }
       $offerRequest = $request->validated();

        $offerId = Offer::query()->find($id);

       if($offerId){
           try {
               $offer = $this->offerService->update($offerRequest,$id);
               if($offer != false){
                   return Response::Success($offer, 'offer updated successfully');
               }else{
                   return Response::Error(false,'you not allowed to update offer');
               }
           }catch (\Exception $exception){
               return Response::Error($exception->getMessage(), $exception->getCode());
           }
       }else{
           return Response::Error(null,"offer not found");
       }
   }

   public function destroy($id)
   {
       $offer = Offer::query()->find($id);
       if(!$offer){
           return Response::Error(null,'offer not found');
       }
       $deleted = $offer->delete();
       return Response::Success($deleted, 'offer deleted successfully');
   }

}
