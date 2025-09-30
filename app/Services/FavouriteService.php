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
            $service = Service::query()->findOrFail($id);

            $existsFavourite = Favorite::query()->where('user_id', $user_id)->where('service_id', $service->id)->exists();
            if($existsFavourite){
                throw new \Exception('Favourite already exists');
            }
            return Favorite::create([
                'user_id'   => $user_id,
                'service_id'=> $service->id,
            ]);
        }

        public function removeFavourite($id){

            $favourite = Favorite::query()->findOrFail($id);

            if(Auth::id()!=$favourite->user_id){
                throw new \Exception('You do not have permission to remove this favourite');
            }
            $favourite->delete();
            return $favourite;

        }
}
