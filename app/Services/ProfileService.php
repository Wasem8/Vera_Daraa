<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;

class ProfileService
{
    public function editProfile($request)
    {
        $id = auth()->id();
        $user = User::query()->where('id' ,$id)->first();
        $request->validate([
            'full_name' => 'string|nullable|max:255',
            'address' => 'string|nullable|max:255',
            'phone' => 'string|nullable|max:20',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $profile = $user->profile?? new Profile();

        $profile->user_id = $id;
        $profile->full_name = $request->full_name;
       $profile->gender = $request->gender;
       $profile->birth_date = $request->birth_date;
        $profile->phone = $request->phone;
        $profile->address = $request->address;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/profile');
            $image->move($destinationPath, $name);
            $profile->image = $name;
        }
            $profile->save();
            return [
                'profile' => $profile,
                'message' => "edit profile successfully",
                'code' => 200];
    }

}








