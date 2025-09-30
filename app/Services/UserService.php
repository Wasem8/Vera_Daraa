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

    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);


        $clientRole = Role::where('name', 'client')->first();
        $user->assignRole($clientRole);

        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $user->givePermissionTo($permissions);

        $user = $this->appendRolesAndPermissions($user);
        $user['token'] = $user->createToken('MyApp')->plainTextToken;

        if (!empty($data['fcm_token'])) {
            $user->deviceTokens()->updateOrCreate(
                ['token' => $data['fcm_token']],
                ['user_id' => $user->id]
            );
        }


        $verificationUrl = URL::temporarySignedRoute(
            'custom.verification.verify',
            now()->addMinutes(60),
            ['id'=> $user->id, 'hash'=> sha1($user->email)]
        );

        Mail::to($user->email)->send(new VerifiedMail($user, $verificationUrl));

        return [
            'user' => $user,
            'message' => 'User registered successfully. Please verify your email.'
        ];
    }


    public function login(array $data, string $role): array
    {
        $user = User::query()->where('email', $data['email'])->first();
        if(!$user){
            return ['user'=>null,'message'=>'user not found','code'=>404];
        }
        $fcmToken = $data['fcm_token'] ?? null;
        unset($data['fcm_token']);
        if(!Auth::attempt($data)){
            return ['user' => null, 'message' => 'Invalid email or password', 'code' => 401];
        }
        if (!$user->hasVerifiedEmail()) {
            return ['user' => false, 'message' => 'Please verify your email first', 'code' => 401];
        }
        if (!$user->hasRole($role)) {
            return ['user' => null, 'message' => 'You do not have the required role', 'code' => 403];
        }

        $user['token'] = $user->createToken('MyApp')->plainTextToken;

        if (!empty($fcmToken)) {
            $user->deviceTokens()->updateOrCreate(
                ['token' => $fcmToken],
                ['user_id' => $user->id]
            );
        }


        return ['user' => $user, 'message' => 'Logged in successfully', 'code' => 200];
    }

    public function logout(): array
    {
        $user = Auth::user();

        if ($user) {
            $user->currentAccessToken()->delete();
            return ['user' => $user, 'message' => 'Logout successfully', 'code' => 200];
        }
        return ['user' => null, 'message' => 'Invalid token', 'code' => 404];
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
