<?php

namespace App\Services;

use App\Mail\VerifiedMail;
use App\Models\User;
use http\Message;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Request;
use Spatie\Permission\Models\Role;

class UserService
{
    public function register($request): array
    {
        $user = User::query()->create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
        ]);

        $clientRole = Role::query()->where('name', 'client')->first();
        $user->assignRole($clientRole);

        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $user->givePermissionTo($permissions);

        $user->load('roles','permissions');

    $user = User::query()->find($user->id);
    $user = $this->appendRolesAndPermissions($user);
    $user['token'] = $user->createToken('MyApp')->plainTextToken;

        if (isset($request['fcm_token'])) {
            $user->deviceTokens()->updateOrCreate(
                ['token' => $request['fcm_token']],
                ['user_id' => $user->id]
            );
        }

        $verificationUrl = URL::temporarySignedRoute(
            'custom.verification.verify',
            now()->addMinutes(60),
            ['id'=> $user->id, 'hash'=> sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerifiedMail($user, $verificationUrl));

        $message = "user registered successfully please verify Email";
    return ['user'=>$user,'message'=> $message];
    }



    public function login($request, string $role): array
    {
        $user = User::query()->where('email', Request::get('email'))->first();
        if(!is_null($user)) {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return [
                    'user' => null,
                    'message' =>"Invalid email or password",
                'code' => 401];
            } else {

                if($user->email_verified_at == null){
                    return [
                        'user' => false,
                        'message' => 'verify your email first',
                        'code' => 401,
                    ];
                }else {
                    if (!$user->hasRole($role)) { return [
                        'user' => null,
                        'message' => "noun",
                        'code' => 200];
                    }
                    $user->load('roles','permissions');
                    $user = $this->appendRolesAndPermissions($user);
                    $user['token'] = $user->createToken('MyApp')->plainTextToken;

                    if (isset($request['fcm_token'])) {
                        $user->deviceTokens()->updateOrCreate(
                            ['token' => $request['fcm_token']],
                            ['user_id' => $user->id]
                        );
                    }

                    return [
                        'user' => $user,
                        'message' => "Logged in successfully",
                        'code' => 200];

                }
            }
        }else{
            return [
                'user' => null,
                'message' => "User not found",
                'code' => 404];
            }

    }

    public function logout(): array
    {
        $user = Auth::user();
        if(!is_null($user)){
            Auth::user()->currentAccessToken()->delete();
            $message = "logout successfully";
            $code = 200;
        }else{
            $message = "Invalid token";
            $code = 404;
        }
        return ['user'=>$user,'message'=>$message,'code'=>$code];

    }



    private function appendRolesAndPermissions($user)
    {
        $roles = $user->roles->pluck('name');
        unset ($user->roles);
        $user['roles'] = $roles;
        $permissions = $user->permissions->pluck('name');
        unset ($user->permissions);
//        $user['permissions'] = $permissions;

        return $user;
    }

}
