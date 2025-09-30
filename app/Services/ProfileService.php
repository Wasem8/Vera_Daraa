<?php

namespace App\Services;

use App\Http\Requests\ProfileRequest;
use App\Http\Responses\Response;
use App\Models\Profile;
use App\Models\User;

class ProfileService
{
    public function editProfile($request)
    {
        $id = auth()->id();
        $user = User::query()->where('id' ,$id)->first();

        $profile = $user->profile?? new Profile();

        $profile->user_id = $id;
        $profile->full_name = $request->full_name;
        $profile->gender = $request->gender;
        $profile->birth_date = $request->birth_date;
        $profile->phone = $request->phone;
        $profile->address = $request->address;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $profile->image = asset('storage/' . $path);
        }

        $profile->save();
            return [
                'profile' => $profile,
                'message' => "edit profile successfully",
                'code' => 200];
    }
    public function updateByAdmin(ProfileRequest $request,$user_id)
    {
        $user = User::query()->findOrFail($user_id);

        $profile = $user->profile??new Profile();
        $profile->user_id = $user_id;
        $profile->full_name = $request['full_name'];
        $profile->gender = $request['gender'];
        $profile->birth_date = $request['birth_date'];
        $profile->phone = $request['phone'];
        $profile->address = $request['address'];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('profiles', 'public');
            $profile->image = asset('storage/' . $path);
        }

        $profile->save();

        return [
            'profile' => $profile,
            'message' => "Client profile updated by admin successfully",
            'code' => 200
        ];
    }


}








