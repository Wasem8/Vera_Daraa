<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\Client_Favorite;
use App\Services\FavouriteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouriteController extends Controller
{
    private $favouriteService;

    public function __construct(FavouriteService $favouriteService)
    {
        $this->favouriteService = $favouriteService;
    }
    public function addFavourite($id)
    {
        $data = [];

        try {
            $data = $this->favouriteService->addFavourite($id);
            return Response::Success($data['favourite'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function removeFavourite($id){
        $data = [];
        try {
            $data = $this->favouriteService->removeFavourite($id);
            return Response::Success($data['favourite'],$data['message']);
        }
        catch (\Exception $exception){
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function favourites()
    {
        $id = Auth::id();
        $favourites = Client_Favorite::query()->where('user_id', $id)->get();
        return Response::Success($favourites,'success');
    }

    public function favourite($id){
        $user_id = Auth::id();
        $favourite = Client_Favorite::query()->where('user_id', $user_id)->first();
        return Response::Success($favourite,'success');
    }
}
