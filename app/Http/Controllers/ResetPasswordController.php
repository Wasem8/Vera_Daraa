<?php

namespace App\Http\Controllers;


use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use App\Models\User;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function userForgetPassword(Request $request){
    $data = $request->validate([
            'email' => 'required|email|exists:users,email']);
        ResetCodePassword::query()->where('email', $request['email'])->delete();

        $data['code'] = mt_rand(100000, 999999);
        $codeData = ResetCodePassword::query()->create($data);
        Mail::to($request['email'])->send(new SendCodeResetPassword($codeData['code']));

        return response()->json(['message'=> trans('passwords.sent')]);

    }

        public function userCheckCode(Request $request)
        {
            $data = $request->validate([
                'code' => 'required|string|exists:reset_code_passwords,code'
            ]);

            $passwordReset = ResetCodePassword::query()->firstWhere('code', $request['code']);

            if ($passwordReset['created_at']->addMinutes(60)->isPast()) {
                $passwordReset->delete();
                return response()->json(['message'=> trans('passwords.code_is_expired')]);
            }
            return response()->json([
                'code'=>$passwordReset['code'],
                'message'=> trans('passwords.code_is_valid')]);

        }


        public function userResetPassword(Request $request){
            $data = $request->validate([
                'code' => 'required|string|exists:reset_code_passwords,code',
                'password' => 'required|string|confirmed'
            ]);

            $passwordReset = ResetCodePassword::query()->firstWhere('code', $request['code']);

            if ($passwordReset['created_at']->addMinutes(60)->isPast()) {
                $passwordReset->delete();
                return response()->json(['message'=> trans('passwords.code_is_expired')]);
            }

            $user = User::query()->firstWhere('email', $passwordReset['email']);

            $input['password'] = bcrypt($request['password']);
            $user->update($input);

            $passwordReset->delete();

            return response()->json(['message'=> 'password changed successfully']);

        }

}
