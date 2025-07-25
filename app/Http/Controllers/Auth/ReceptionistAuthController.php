<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Services\UserService;
use Illuminate\Http\Request;

class ReceptionistAuthController extends Controller
{
    private UserService $userService;
    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public function receptionistLogin(Request $request)
    {
        $data = [];
        try {

            $data = $this->userService->login($request,'receptionist');
            return Response::Success($data['user'], $data['message'],$data['code']);
        }
        catch (\Throwable $exception){
            $message = $exception->getMessage();
            return Response::Error($data,$message);
        }
    }

    public function receptionistLogout(){
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
