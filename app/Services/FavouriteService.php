<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class FavouriteService
{
        public function addFavourite($id)
        {
            $user_id = Auth::user()->id;
            $service = Service::query()->find($id);
            if(!$service){
                return [
                    'favourite'=>null,
                    'message'=>'service not found'];
            }
            $existsFavourite = Favorite::query()->where('user_id', $user_id)->where('service_id', $service->id)->first();
            if($existsFavourite){
                return [
                    'favourite'=>null,
                    'message'=>'favourite already exist'
                ];
            }
            $favourite = Favorite::query()->create([
                'user_id' => $user_id,
                'service_id' => $service->id,
            ]);
            return [
                'favourite'=>$favourite,
                'message'=>'add favourite success'];
        }

        public function removeFavourite($id){

            $favourite = Favorite::query()->find($id);
            if(!$favourite){
                return [
                    'favourite'=>null,
                    'message'=>'favourite not found'];
            }
            if(Auth::id()!=$favourite->user_id){
                return [
                    'favourite'=>false,
                    'message'=>'you do not have permission to remove this favourite'
                ];
            }
            $favourite->delete();
            return [
                'favourite'=>$favourite,
                'message'=>'remove favourite success'];

        }
}
