<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentsController;
use App\Http\Controllers\Api\SuppliersController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\ModelsController;
use App\Http\Controllers\Api\DocTypeGroupsController;
use App\Http\Controllers\Api\DocTypeSubCategoriesController;
use App\Http\Controllers\Api\FileExtensionsController;
use App\Http\Controllers\Api\CategoryActivitiesController;
use App\Http\Controllers\Api\ProjectStatusController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\PartGroupsController;
use App\Http\Controllers\Api\StampFormatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserMaintenanceController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return app(AuthController::class)->showLoginForm();
})->name('login');


Route::post('/login', [AuthController::class, 'login'])->name('login_post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');




Route::middleware(['auth'])->group(function () {


    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/file-manager.upload', function () {
        return view('file_management.file_upload');
    })->name('file-manager.upload');

    Route::get('/file-manager.export', function () {
        return view('file_management.file_export');
    })->name('file-manager.export');

    Route::get('/approval', function () {
        return view('approvals.approval');
    })->name('approval');

    Route::get('/settings', function () {
        return view('master.setting');
    })->name('settings');

    Route::get('/stampFormat', function () {
        return view('master.stampFormat');
    })->name('stampFormat');

    Route::get('/user-maintenance', function () {
        return view('user_maintenance.user_maintenance');
    })->name('user-maintenance');


    // Data Master Routes


    Route::get('/departments', function () {
        return view('master.departments');
    })->name('departments');

    Route::get('/suppliers', function () {
        return view('master.supplier');
    })->name('suppliers');

    Route::get('/customers', function () {
        return view('master.customer');
    })->name('customers');

    Route::get('/models', function () {
        return view('master.model');
    })->name('models');

    Route::get('/docTgroups', function () {
        return view('master.docTgroup');
    })->name('docTgroups');

    Route::get('/docTsubCategories', function () {
        return view('master.docTsubCategory');
    })->name('docTsubCategories');

    Route::get('/fileExtensions', function () {
        return view('master.fileExtension');
    })->name('fileExtensions');

    Route::get('/categoryActivities', function () {
        return view('master.fileExtension');
    })->name('categoryActivities');

    Route::get('/categoryActivities', function () {
        return view('master.categoryActivity');
    })->name('categoryActivities');

    Route::get('/projectStatus', function () {
        return view('master.projectStatus');
    })->name('projectStatus');

    Route::get('/partGroups', function () {
        return view('master.partGroup');
    })->name('partGroups');

    Route::get('/stampFormat', function () {
        return view('master.stampFormat');
    })->name('stampFormat');

    Route::get('/menu-management', function () {
        return view('master.menu_management');
    })->name('menu-management');

    Route::get('stampFormat/data', [StampFormatController::class, 'data'])->name('stampFormat.data');
    Route::resource('master/stampFormat', StampFormatController::class)->names('stampFormat')->except(['create', 'edit']);
    Route::resource('master/departments', DepartmentsController::class)->names('departments')->except(['create', 'edit']);
    Route::get('/departments/data', [DepartmentsController::class, 'data'])->name('departments.data');
    Route::resource('master/suppliers', SuppliersController::class)->names('suppliers')->except(['create', 'edit']);
    Route::get('/suppliers/data', [SuppliersController::class, 'data'])->name('suppliers.data');
    Route::resource('master/customers', CustomersController::class)->names('customers')->except(['create', 'edit']);
    Route::get('/customers/data', [CustomersController::class, 'data'])->name('customers.data');
    Route::resource('master/models', ModelsController::class)->names('models')->except(['create', 'edit']);
    Route::get('/models/data', [ModelsController::class, 'data'])->name('models.data');
    Route::get('/models/get-customers', [ModelsController::class, 'getCustomers'])->name('models.getCustomers');
    Route::resource('master/docTypeGroups', DocTypeGroupsController::class)->names('docTypeGroups')->except(['create', 'edit']);
    Route::get('/docTypeGroups/data', [DocTypeGroupsController::class, 'data'])->name('docTypeGroups.data');
    Route::resource('master/docTypeSubCategories', DocTypeSubCategoriesController::class)->names('docTypeSubCategories')->except(['create', 'edit']);
    Route::get('/docTypeSubCategories/data', [DocTypeSubCategoriesController::class, 'data'])->name('docTypeSubCategories.data');
    Route::get('/docTypeSubCategories/getDocTypeGroups', [DocTypeSubCategoriesController::class, 'getDocTypeGroups'])->name('docTypeSubCategories.getDocTypeGroups');
    Route::resource('master/fileExtensions', FileExtensionsController::class)->names('fileExtensions')->except(['create', 'edit']);
    Route::get('/fileExtensions/data', [FileExtensionsController::class, 'data'])->name('fileExtensions.data');
    Route::resource('master/categoryActivities', CategoryActivitiesController::class)->names('categoryActivities')->except(['create', 'edit']);
    Route::get('/categoryActivities/data', [CategoryActivitiesController::class, 'data'])->name('categoryActivities.data');
    Route::resource('master/projectStatus', ProjectStatusController::class)->names('projectStatus')->except(['create', 'edit']);
    Route::get('/projectStatus/data', [ProjectStatusController::class, 'data'])->name('projectStatus.data');
    Route::resource('master/partGroups', PartGroupsController::class)->names('partGroups')->except(['create', 'edit']);
    Route::get('/partGroups/data', [PartGroupsController::class, 'data'])->name('partGroups.data');
    Route::get('/partGroups/getModelsByCustomer', [PartGroupsController::class, 'getModelsByCustomer'])->name('partGroups.getModelsByCustomer');
    Route::resource('master/userMaintenance', UserMaintenanceController::class)->only(['store', 'show', 'update', 'destroy'])->parameters(['userMaintenance' => 'user'])->names('userMaintenance');
    Route::get('userMaintenance/data', [UserMaintenanceController::class, 'data'])->name('userMaintenance.data');
    Route::resource('master/menus', MenuController::class)->names('menus')->except(['create', 'edit', 'index']);
    Route::get('/menus/data', [MenuController::class, 'data'])->name('menus.data');
    Route::get('/menus/get-parents', [MenuController::class, 'getParents'])->name('menus.getParents');


    Route::post('/dashboard/getDocumentGroups', [DashboardController::class, 'getDocumentGroups'])->name('dashboard.getDocumentGroups');
    Route::post('/dashboard/getSubType', [DashboardController::class, 'getSubType'])->name('dashboard.getSubType');
    Route::post('/dashboard/getCustomer', [DashboardController::class, 'getCustomer'])->name('dashboard.getCustomer');
    Route::post('/dashboard/getModel', [DashboardController::class, 'getModel'])->name('dashboard.getModel');
    Route::post('/dashboard/getPartGroup', [DashboardController::class, 'getPartGroup'])->name('dashboard.getPartGroup');
    Route::post('/dashboard/getStatus', [DashboardController::class, 'getStatus'])->name('dashboard.getStatus');



    Route::post('upload.getDataCustomer', [DashboardController::class, 'getCustomer'])->name('upload.getDataCustomer');
});
