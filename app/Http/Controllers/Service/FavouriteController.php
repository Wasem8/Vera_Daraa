<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\_Favorite;
use App\Models\Favorite;
use App\Services\FavouriteService;
use Illuminate\Support\Facades\Auth;

class FavouriteController extends Controller
{
    private $favouriteService;

    public function __construct(FavouriteService $favouriteService)
    {
        $this->favouriteService = $favouriteService;
    }
    public function store($serviceId)
    {
        try {
            $favourite = $this->favouriteService->addFavourite($serviceId);
            return Response::Success($favourite, 'Added to favourites successfully');
        } catch (\Exception $e) {
            return Response::Error(null, $e->getMessage(), 400);
        }
    }

    public function destroy($favouriteId)
    {
        try {
            $favourite = $this->favouriteService->removeFavourite($favouriteId);
            return Response::Success($favourite, 'Removed from favourites successfully');
        } catch (\Exception $e) {
            return Response::Error(null, $e->getMessage(), 400);
        }
    }

    public function index()
    {
        $id = Auth::id();
        $favourites = Favorite::query()->with('service')->where('user_id', $id)->get();
        return Response::Success($favourites,'success');
    }

    public function show($id){
        $favourite = Favorite::query()->with('service')
            ->where('user_id', Auth::id())
           ->findOrFail($id);
        if (!$favourite) {
            return Response::Error(null, 'Favourite not found', 404);
        }


        return Response::Success($favourite,'success');
    }
}
