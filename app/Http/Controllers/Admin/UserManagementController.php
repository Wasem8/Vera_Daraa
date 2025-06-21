<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(){
        $users = User::query()->whereNotIn('id',[auth()->id()] )->get();
        return Response::Success($users,'Users List');
    }

    public function toggleStatus(User $user)
    {
        if($user->hasRole('admin') && $user->id == 1){
            return Response::Error('false','the status of admin can not be set');
        }
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'true' : 'false';
        return Response::Success($status,'User status changed');
    }
}
