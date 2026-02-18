<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

Route::get('/debug-session-access', function () {
    $user = auth()->user();
    if (!$user) {
        return "User not logged in.";
    }
    
    $sessionMenus = session('allowed_menus', []);
    
    echo "<h1>Debug Access Info</h1>";
    echo "<p>User: " . $user->name . " (ID: " . $user->id . ")</p>";
    echo "<p>Allowed Menu IDs in Session:</p>";
    echo "<pre>" . print_r($sessionMenus, true) . "</pre>";
    
    echo "<hr>";
    echo "<p>Checking Menu ID 3 (Drawing Upload): " . (in_array(3, $sessionMenus) ? "ALLOWED" : "DENIED") . "</p>";
    echo "<p>Checking Menu ID 4 (Export): " . (in_array(4, $sessionMenus) ? "ALLOWED" : "DENIED") . "</p>";
    
})->middleware('auth');
