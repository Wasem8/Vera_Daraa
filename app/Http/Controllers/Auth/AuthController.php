<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UsersSignupRequest;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(UsersSignupRequest $request)
    {
        $data = [];


        try {
            $data = $this->userService->register($request->validated());
            return Response::Success($data['user'], $data['message']);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }


    public function clientLogin(Request $request)
    {
        $data = [];


        try {
            $data = $this->userService->login($request, 'client');
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }

    public function clientLogout()
    {
        $data = [];
        try {
            $data = $this->userService->logout();
            return Response::Success($data['user'], $data['message'], $data['code']);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }


    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::query()->find($id);
        if (!$user) {
            return Response::success('false', 'user not found');
        }

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return Response::success('false', 'wrong hash');
        }

        if ($user->hasVerifiedEmail()) {
            return Response::success('false', 'email already verified');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));
        //    $request->fulfill();
//    event(new Verified(User::query()->findOrFail($request->route('id'))));
        return Response::success('true', 'Email verified');
    }


    public function resendEmail(Request $request)
    {
        $user = User::query()->where('email', $request->email)->first();
        if (!$user) {
            return Response::success('false', 'user not found');
        }


        if ($user->hasVerifiedEmail()) {
            return Response::Error('false', 'email already verified');
        }


        event(new Registered($user));
        //    $request->fulfill();
//    event(new Verified(User::query()->findOrFail($request->route('id'))));
        return Response::success('true', 'Email sent');
    }

}
