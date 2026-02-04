<?php

namespace App\Http\Controllers\Api;

use App\Models\DwgProfile;
use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function getSystemProfile()
    {
        $system = DwgProfile::first();

        return response()->json($system);
    }
}
