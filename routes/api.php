<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::get('/active-users-count', [DashboardController::class, 'getActiveUsersCount']);
Route::get('/log-data', [DashboardController::class, 'getDataLog'])->name('api.getDataLog');
Route::get('/log-data-activity', [DashboardController::class, 'getDataActivityLog'])->name('api.getDataActivityLog');
Route::get('/upload-monitoring-data', [DashboardController::class, 'getUploadMonitoringData'])->name('api.upload-monitoring-data');
Route::get('/upload-monitoring-data-project', [DashboardController::class, 'getUploadMonitoringDataProject'])->name('api.upload-monitoring-data-project');
