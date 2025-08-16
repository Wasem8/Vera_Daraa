<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index(){
        if(Auth::user()->hasRole('admin')) {
            $users = User::query()->whereNotIn('id', [auth()->id()])->get();
            return Response::Success($users, 'Users List');
        }else{
            return Response::Error(false,'Unauthorized');
        }
    }


    public function show($id)
    {
        if(Auth::user()->hasRole(['admin','receptionist'])) {
            $users = User::query()->where('id',$id)->get();
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
            return Response::Success($user, 'User');
        }else{
            return Response::Error(false,'Unauthorized');
        }
    }
}
