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
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\RoleMenuController;
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


    Route::get('/user-maintenance', function () {
        return view('user_management.user_maintenance');
    })->name('user-maintenance');

    Route::get('/role', function () {
        return view('user_management.role');
    })->name('role');




    Route::get('/role-menu', function () {
        return view('user_management.role_menu');
    })->name('role-menu');






    Route::get('/user-role', function () {
        return view('user_management.user_role');
    })->name('user-role');

    Route::get('/product', function () {
        return view('master.product');
    })->name('product');


    #Region Stamp Format
    Route::get('stampFormat/data', [StampFormatController::class, 'data'])->name('stampFormat.data');
    Route::resource('master/stampFormat', StampFormatController::class)->names('stampFormat')->except(['create', 'edit']);
    #End region

    #Region Departement
    Route::resource('master/departments', DepartmentsController::class)->names('departments')->except(['create', 'edit']);
    Route::get('/departments/data', [DepartmentsController::class, 'data'])->name('departments.data');
    #End region

    #Region Suppliers
    Route::resource('master/suppliers', SuppliersController::class)->names('suppliers')->except(['create', 'edit']);
    Route::get('/suppliers/data', [SuppliersController::class, 'data'])->name('suppliers.data');
    #End region

    #Region Customers
    Route::resource('master/customers', CustomersController::class)->names('customers')->except(['create', 'edit']);
    Route::get('/customers/data', [CustomersController::class, 'data'])->name('customers.data');
    #End region

    #Region Model
    Route::resource('master/models', ModelsController::class)->names('models')->except(['create', 'edit']);
    Route::get('/models/data', [ModelsController::class, 'data'])->name('models.data');
    Route::get('/models/get-customers', [ModelsController::class, 'getCustomers'])->name('models.getCustomers');
    #End region

    #Region Doc Type Group
    Route::resource('master/docTypeGroups', DocTypeGroupsController::class)->names('docTypeGroups')->except(['create', 'edit']);
    Route::get('/docTypeGroups/data', [DocTypeGroupsController::class, 'data'])->name('docTypeGroups.data');
    #End region

    #Region Doc Sub Category
    Route::resource('master/docTypeSubCategories', DocTypeSubCategoriesController::class)->names('docTypeSubCategories')->except(['create', 'edit']);
    Route::get('/docTypeSubCategories/data', [DocTypeSubCategoriesController::class, 'data'])->name('docTypeSubCategories.data');
    Route::get('/docTypeSubCategories/getDocTypeGroups', [DocTypeSubCategoriesController::class, 'getDocTypeGroups'])->name('docTypeSubCategories.getDocTypeGroups');

    #New
    Route::get('/docTypeSubCategories/get-customers', [DocTypeSubCategoriesController::class, 'getCustomers'])->name('docTypeSubCategories.getCustomers');
    Route::get('/master/docTypeSubCategories/{subcategoryId}/aliases', [DocTypeSubCategoriesController::class, 'aliases'])->name('docTypeSubCategories.aliases.data');
    Route::post('/master/aliases', [DocTypeSubCategoriesController::class, 'storeAlias'])->name('docTypeSubCategories.aliases.store');
    Route::get('/master/aliases/{alias}', [DocTypeSubCategoriesController::class, 'showAlias'])->name('docTypeSubCategories.aliases.show');
    Route::put('/master/aliases/{alias}', [DocTypeSubCategoriesController::class, 'updateAlias'])->name('docTypeSubCategories.aliases.update');
    Route::delete('/master/aliases/{alias}', [DocTypeSubCategoriesController::class, 'destroyAlias'])->name('docTypeSubCategories.aliases.destroy');
    #End region

    #Region File Extension
    Route::resource('master/fileExtensions', FileExtensionsController::class)->names('fileExtensions')->except(['create', 'edit']);
    Route::get('/fileExtensions/data', [FileExtensionsController::class, 'data'])->name('fileExtensions.data');
    #End region

    #Region Category Activities
    Route::resource('master/categoryActivities', CategoryActivitiesController::class)->names('categoryActivities')->except(['create', 'edit']);
    Route::get('/categoryActivities/data', [CategoryActivitiesController::class, 'data'])->name('categoryActivities.data');
    #End region

    #Region Project Status
    Route::resource('master/projectStatus', ProjectStatusController::class)->names('projectStatus')->except(['create', 'edit']);
    Route::get('/projectStatus/data', [ProjectStatusController::class, 'data'])->name('projectStatus.data');
    #End region

    #Region Part Group
    Route::resource('master/partGroups', PartGroupsController::class)->names('partGroups')->except(['create', 'edit']);
    Route::get('/partGroups/data', [PartGroupsController::class, 'data'])->name('partGroups.data');
    Route::get('/partGroups/getModelsByCustomer', [PartGroupsController::class, 'getModelsByCustomer'])->name('partGroups.getModelsByCustomer');

    #End region

    #Region User Maintenance
    Route::resource('master/userMaintenance', UserMaintenanceController::class)->only(['store', 'show', 'update', 'destroy'])->parameters(['userMaintenance' => 'user'])->names('userMaintenance');
    Route::get('userMaintenance/data', [UserMaintenanceController::class, 'data'])->name('userMaintenance.data');
    #End region

    #Region Menu
    Route::resource('master/menus', MenuController::class)->names('menus')->except(['create', 'edit', 'index']);
    Route::get('/menus/data', [MenuController::class, 'data'])->name('menus.data');
    Route::get('/menus/get-parents', [MenuController::class, 'getParents'])->name('menus.getParents');
    #End region

    #Region Dashboard
    Route::post('/dashboard/getDocumentGroups', [DashboardController::class, 'getDocumentGroups'])->name('dashboard.getDocumentGroups');
    Route::post('/dashboard/getSubType', [DashboardController::class, 'getSubType'])->name('dashboard.getSubType');
    Route::post('/dashboard/getCustomer', [DashboardController::class, 'getCustomer'])->name('dashboard.getCustomer');
    Route::post('/dashboard/getModel', [DashboardController::class, 'getModel'])->name('dashboard.getModel');
    Route::post('/dashboard/getPartGroup', [DashboardController::class, 'getPartGroup'])->name('dashboard.getPartGroup');
    Route::post('/dashboard/getStatus', [DashboardController::class, 'getStatus'])->name('dashboard.getStatus');
    #End region

    #Region User Role
    Route::get('/user-role/pair', [UserRoleController::class, 'pairShow'])->name('user-role.pairShow');
    Route::put('/user-role/pair', [UserRoleController::class, 'pairUpdate'])->name('user-role.pairUpdate');
    Route::delete('/user-role/pair', [UserRoleController::class, 'pairDestroy'])->name('user-role.pairDestroy');
    Route::get('/user-role/data',      [UserRoleController::class, 'data'])->name('user-role.data');
    Route::get('/user-role/dropdowns', [UserRoleController::class, 'dropdowns'])->name('user-role.dropdowns');
    Route::resource('user_management/user-role', UserRoleController::class)->names('user-role')->except(['create', 'edit', 'show']);
    #End region

    #Region Role
    Route::resource('user_management/role', RoleController::class)->names('role')->except(['create', 'edit']);
    Route::get('/role/data', [RoleController::class, 'data'])->name('role.data');
    Route::get('/role/get-models', [RoleController::class, 'getModels'])->name('role.getModels');
    #End region

    #Region master
    Route::resource('master/products', ProductsController::class)->names('products')->except(['create', 'edit']);
    Route::get('/products/data', [ProductsController::class, 'data'])->name('products.data');
    Route::get('/products/get-models', [ProductsController::class, 'getModels'])->name('products.getModels');
    #End region

    #Region upload
    Route::post('upload.getCustomerData', [UploadController::class, 'getCustomerData'])->name('upload.getCustomerData');
    Route::post('upload.getModelData', [UploadController::class, 'getModelData'])->name('upload.getModelData');
    #End region

    #Region Role Menu
    Route::get('/role-menu/pair', [RoleMenuController::class, 'pairShow'])->name('role-menu.pairShow');
    Route::put('/role-menu/pair', [RoleMenuController::class, 'pairUpdate'])->name('role-menu.pairUpdate');
    Route::delete('/role-menu/pair', [RoleMenuController::class, 'pairDestroy'])->name('role-menu.pairDestroy');

    Route::get('/role-menu/data', [RoleMenuController::class, 'data'])->name('role-menu.data');        
    Route::get('/role-menu/dropdowns', [RoleMenuController::class, 'dropdowns'])->name('role-menu.dropdowns'); 
    Route::resource('user_management/role-menu', RoleMenuController::class)->names('role-menu')->except(['create', 'edit', 'show']);
    Route::get('/user-role/by-user/{user}', [UserRoleController::class, 'byUser'])->name('user-role.byUser');
    Route::post('/user-role/by-user/{user}', [UserRoleController::class, 'sync'])->name('user-role.sync');
    Route::get('/role-menu/by-user/{user}', [RoleMenuController::class, 'byUser'])->name('role-menu.byUser');
    Route::post('/role-menu/by-user/{user}', [RoleMenuController::class, 'syncByUser'])->name('role-menu.syncByUser');
});
