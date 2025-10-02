<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::get('/active-users-count', [DashboardController::class, 'getActiveUsersCount']);
