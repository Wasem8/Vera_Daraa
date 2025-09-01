<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UsersSignupRequest;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function index(){
        if(Auth::user()->hasRole('admin')) {
            $users = User::query()->with('bookings.invoice')->whereNotIn('id', [auth()->id()])->get();
            $clients = $users->where('role', 'client');
            return Response::Success($clients, 'Clients List');
        }else{
            return Response::Error(false,'Unauthorized');
        }
    }


    public function show($id)
    {
        if(Auth::user()->hasRole(['admin','receptionist'])) {
            $users = User::query()->with('bookings.invoice')->where('id',$id)->get();
            return Response::Success($users, 'Users List');
        }else{
            return Response::Error(false,'Unauthorized');
        }
    }

    public function toggleStatus($userId)
    {
        $user = User::query()->find($userId);
        if(!$user) {
            return Response::Error(false,'User not found');
        }
        if($user->hasRole('admin') && $user->id == 1){
            return Response::Error('false','the status of admin can not be set');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'true' : 'false';
        return Response::Success($status,'User status changed');
    }



    public function searchUser(Request $request)
    {

        if(Auth::user()->hasRole(['admin','receptionist'])) {
            $user = User::where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')->get();
            $client = $user->where('role', 'client');
            return Response::Success($client, 'Client');
        }else{
            return Response::Error(false,'Unauthorized');
        }
    }

    public function storeUser(StoreUserRequest $request)
    {
        $tempPassword = Str::random(8);
       $user = User::query()->create([
           'name'=> $request->name,
           'email' => $request->email,
           'password' => Hash::make($tempPassword),
       ]);
        $clientRole = Role::query()->where('name', 'client')->first();
        $user->assignRole($clientRole);

        $permissions = $clientRole->permissions()->pluck('name')->toArray();
        $user->givePermissionTo($permissions);


        return Response::Success($user, 'User created');
    }
}
