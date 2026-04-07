<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DwgProfile;

class SettingProfileController extends Controller
{
    public function edit()
    {
        $profile = DwgProfile::first() ?? new DwgProfile();
        return view('setProfile.setProfile', compact('profile'));
    }

    public function update(Request $request)
    {
       
        $request->validate([
            'app_name' => 'required|string|max:150',
            'app_version' => 'required|string|max:50',
            'app_description' => 'nullable|string',
            'logo_description' => 'nullable|string',
        ]);

       
        $profile = DwgProfile::first();
        if (!$profile) {
            $profile = new DwgProfile();
        }

        $profile->app_name = $request->app_name;
        $profile->app_version = $request->app_version;
        $profile->app_description = $request->app_description;
        $profile->logo_description = $request->logo_description;

        $profile->save();

        return redirect()->route('setProfile')->with('success', 'System profile updated successfully!');
    }
}