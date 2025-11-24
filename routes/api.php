<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;

Route::get('/active-users-count', [DashboardController::class, 'getActiveUsersCount']);
Route::get('/upload-count', [DashboardController::class, 'getUploadCount']);
Route::get('/download-count', [DashboardController::class, 'getDownloadCount']);
Route::get('/doc-count', [DashboardController::class, 'getDocCount']);
Route::get('/log-data', [DashboardController::class, 'getDataLog'])->name('api.trend-upload-download');
Route::get('/log-data-activity', [DashboardController::class, 'getDataActivityLog'])->name('api.getDataActivityLog');
Route::get('/upload-monitoring-data', [DashboardController::class, 'getUploadMonitoringData'])->name('api.upload-monitoring-data');






Route::get('/upload-phase-status', [DashboardController::class, 'getPhaseStatus'])->name('api.upload-phase-status');






Route::get('/disk-space', [DashboardController::class, 'getDiskSpace'])->name('api.disk-space');
