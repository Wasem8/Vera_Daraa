<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Responses\Response;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
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
        $clients = User::role('client')
            ->with('bookings.invoice')
            ->whereNotIn('id', [auth()->id()])
            ->get();

        return Response::Success($clients, 'Clients List');
    }


    public function show($id)
    {
        $user = User::with('bookings.invoice')->findOrFail($id);

        return Response::Success($user, 'User Details');
    }

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('admin') && $user->id == 1) {
            return Response::Error(null, 'The main admin status cannot be changed', 400);
        }

        $user->update(['is_active' => !$user->is_active]);

        return Response::Success(
            ['is_active' => $user->is_active],
            'User status changed'
        );
    }



    public function searchUser(Request $request)
    {

        $query = $request->get('search', '');

        $clients = User::role('client')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->get();

        return Response::Success($clients, 'Client search results');
    }

    public function store(StoreUserRequest $request)
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
