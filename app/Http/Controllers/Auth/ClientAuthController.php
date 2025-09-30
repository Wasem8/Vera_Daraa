<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UsersSignupRequest;
use App\Http\Responses\Response;
use App\Mail\VerifiedMail;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ClientAuthController extends Controller
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
            $validated = $request->validated();
            $data = $this->userService->register($validated);
            return Response::Success($data['user'], $data['message']);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();
            return Response::Error($data, $message);
        }
    }


    public function clientLogin(UserLoginRequest $request)
    {
        $data = [];


        try {
            $validatedData = $request->validated();
            $data = $this->userService->login($validatedData, 'client');
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





    public function resendEmail(Request $request)
    {
        $user = User::query()->where('email', $request->email)->first();
        if (!$user) {
            return Response::success('false', 'user not found');
        }


        if ($user->hasVerifiedEmail()) {
            return Response::Error('false', 'email already verified');
        }

        $verificationUrl = URL::temporarySignedRoute(
            'custom.verification.verify',
            now()->addMinutes(60),
            ['id'=> $user->id, 'hash'=> sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerifiedMail($user, $verificationUrl));
        return Response::success('true', 'Email sent');
    }


    public function customVerify(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if(!$user){
            return  response()->view('Verify.error');
        }

        if (! hash_equals((string) $hash, sha1($user->email))) {
            return response()->json(['success' => false, 'message' => 'رابط تحقق غير صالح'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->view('Verify.error');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->view('Verify.success');
    }



}
