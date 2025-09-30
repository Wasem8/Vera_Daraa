<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfferRequest;
use App\Http\Requests\UpdateOfferRequest;
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
        $offers = Offer::query()->status($request->status)->with('services')->get();
        return Response::Success($offers, 'offers retrieved successfully');


    }

    public function show($id)
    {
        $offer = Offer::with('services')->findOrFail($id);
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
