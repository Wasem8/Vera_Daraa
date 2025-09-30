<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Responses\Response;
use App\Services\ProfileService;

class ProfileController extends Controller
{
    private ProfileService $profileService;

    public function __construct(ProfileService $profileService){
        $this->profileService = $profileService;
    }

    public function editProfile(ProfileRequest $request){
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
        $user = auth()->user()->load('profile');

        if (!$user->profile) {
            return Response::Error(null, 'Create new profile first', 404);
        }

        return Response::Success($user->profile, 'success');
    }



}
