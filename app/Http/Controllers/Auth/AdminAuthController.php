<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Responses\Response;
use App\Services\UserService;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    private UserService $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public function adminLogin(UserLoginRequest $request)
    {
        $data = [];
        try {
            $validated = $request->validated();
            $data = $this->userService->login($validated,'admin');
            return Response::Success($data['user'], $data['message'],$data['code']);
        }
        catch (\Throwable $exception){
            $message = $exception->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function adminLogout(){
        $data = [];
        try {
            $data = $this->userService->logout();
            return Response::Success($data['user'], $data['message'],$data['code']);
        }
        catch (\Throwable $exception){
            $message = $exception->getMessage();
            return Response::Error($data,$message);
        }
    }


}
