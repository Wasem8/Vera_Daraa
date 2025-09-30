<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    private PasswordResetService $service;

    public function __construct(PasswordResetService $service)
    {
        $this->service = $service;
    }

    public function userForgetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $this->service->sendCode($validated['email']);

        return Response::Success(true,'passwords.sent');
    }

    public function userCheckCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|exists:reset_code_passwords,code'
        ]);

        if (!$this->service->verifyCode($validated['code'])) {
            return Response::Error(false,'passwords.code_is_expired');
        }

        return Response::Success(true,'passwords.code_is_valid');
    }

    public function userResetPassword(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|exists:reset_code_passwords,code',
            'password' => 'required|string|confirmed'
        ]);

        if (!$this->service->resetPassword($validated['code'], $validated['password'])) {
            return Response::Error(false,'passwords.code_is_expired');
        }

        return Response::Success(true,'Password changed successfully');
    }
}
