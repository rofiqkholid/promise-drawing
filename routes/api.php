<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::get('/active-users-count', [DashboardController::class, 'getActiveUsersCount']);
Route::get('/upload-count', [DashboardController::class, 'getUploadCount']);
Route::get('/download-count', [DashboardController::class, 'getDownloadCount']);
Route::get('/doc-count', [DashboardController::class, 'getDocCount']);
Route::get('/log-data', [DashboardController::class, 'getDataLog'])->name('api.getDataLog');
Route::get('/log-data-activity', [DashboardController::class, 'getDataActivityLog'])->name('api.getDataActivityLog');
Route::get('/upload-monitoring-data', [DashboardController::class, 'getUploadMonitoringData'])->name('api.upload-monitoring-data');
Route::get('/upload-dashboard-data', [DashboardController::class, 'getUploadDashboardData'])->name('api.upload-dashboard-data');
Route::get('/upload-dashboard-data-project', [DashboardController::class, 'getUploadDashboardDataProject'])->name('api.upload-dashboard-data-project');
Route::get('/disk-space', [DashboardController::class, 'getDiskSpace'])->name('api.disk-space');
