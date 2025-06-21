<?php

namespace App\Services;

use App\Models\Client_Favorite;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class FavouriteService
{
        public function addFavourite($id)
        {
            $user_id = Auth::user()->id;
            $service = Service::query()->find($id);
            if(!is_null($service)){
                $favourite = Client_Favorite::query()->create([
                'user_id' => $user_id,
                'service_id' => $service->id,
            ]);
            return [
                'favourite'=>$favourite,
                'message'=>'add favourite success'];
            }
            return [
                'favourite'=>null,
                'message'=>'service not found'];
        }

        public function removeFavourite($id){
            $user_id = Auth::user()->id;
            $favourite = Client_Favorite::query()->find($id);
            if(!is_null($favourite)){
                $favourite->delete();
                return [
                    'favourite'=>$favourite,
                    'message'=>'remove favourite success'];
            }
            return [
                'favourite'=>null,
                'message'=>'favourite not found'];
        }
}
