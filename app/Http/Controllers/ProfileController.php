<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService){
        $this->profileService = $profileService;
    }

    public function editProfile(Request $request){
        $data = [];
        try {
              $data = $this->profileService->editProfile($request);
        return Response::Success($data['profile'], $data['message']);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function showProfile()
    {
        $id = auth()->id();
        $user = auth()->user();
        $user = User::query()->where('id' ,$id)->first();
        if($user->profile == null){
            return Response::Error(null,'create new profile first');
        }
        else {
            $profile = $user->profile;
            return Response::Success($profile,'success');
        }
    }

}
