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
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\DrawingUploadController;
use App\Http\Controllers\Api\CustomerRevisionLabelController;
use App\Http\Controllers\Api\PackageFormatController;
use App\Http\Controllers\Api\FilePreviewController;
use App\Http\Controllers\Api\ShareController;
use Illuminate\Support\Facades\Auth;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home');
    }
    return app(AuthController::class)->showLoginForm();
})->name('login');

Route::get('/home', [AuthController::class, 'redirectToHomepage'])->middleware('auth')->name('home');

Route::post('/login', [AuthController::class, 'login'])->name('login_post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware(['auth'])->group(function () {


    Route::get('/monitoring', function () {
        return view('monitoring');
    })->middleware(['auth', 'check.menu:1'])->name('monitoring');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'check.menu:28'])->name('dashboard');

    Route::get('/file-manager.upload', function () {
        return view('file_management.file_upload');
    })->middleware(['auth', 'check.menu:3'])->name('file-manager.upload');

    Route::get('/file-manager.export', function () {
        return view('file_management.file_export');
    })->middleware(['auth', 'check.menu:4'])->name('file-manager.export');

    Route::get('/file-manager.share', function () {
        return view('file_management.share');
    })->middleware(['auth', 'check.menu:4'])->name('file-manager.share');


    Route::get('/file-manager.export/{id}', [ExportController::class, 'showDetail'])->middleware(['auth', 'check.menu:4'])->name('file-manager.export.detail');

    Route::get('/approval', function () {
        return view('approvals.approval');
    })->middleware(['auth', 'check.menu:5'])->name('approval');

    // Data Master Routes

    Route::get('/departments', function () {
        return view('master.departments');
    })->middleware(['auth', 'check.menu:9'])->name('departments');

    Route::get('/suppliers', function () {
        return view('master.supplier');
    })->middleware(['auth', 'check.menu:10'])->name('suppliers');

    Route::get('/customers', function () {
        return view('master.customer');
    })->middleware(['auth', 'check.menu:11'])->name('customers');

    Route::get('/models', function () {
        return view('master.model');
    })->middleware(['auth', 'check.menu:12'])->name('models');

    Route::get('/docTgroups', function () {
        return view('master.docTgroup');
    })->middleware(['auth', 'check.menu:13'])->name('docTgroups');

    Route::get('/docTsubCategories', function () {
        return view('master.docTsubCategory');
    })->middleware(['auth', 'check.menu:14'])->name('docTsubCategories');

    Route::get('/fileExtensions', function () {
        return view('master.fileExtension');
    })->middleware(['auth', 'check.menu:15'])->name('fileExtensions');

    Route::get('/categoryActivities', function () {
        return view('master.categoryActivity');
    })->middleware(['auth', 'check.menu:19'])->name('categoryActivities');

    Route::get('/projectStatus', function () {
        return view('master.projectStatus');
    })->middleware(['auth', 'check.menu:17'])->name('projectStatus');

    Route::get('/partGroups', function () {
        return view('master.partGroup');
    })->middleware(['auth', 'check.menu:16'])->name('partGroups');

    Route::get('/menu-management', function () {
        return view('master.menu_management');
    })->middleware(['auth', 'check.menu:20'])->name('menu-management');

    Route::get('/user-maintenance', function () {
        return view('user_management.user_maintenance');
    })->middleware(['auth', 'check.menu:7'])->name('user-maintenance');

    Route::get('/role', function () {
        return view('user_management.role');
    })->middleware(['auth', 'check.menu:22'])->name('role');

    Route::get('/drawing-upload', function () {
        return view('file_management.drawing_upload');
    })->middleware(['auth', 'check.menu:3'])->name('drawing.upload');

    Route::get('/product', function () {
        return view('master.product');
    })->middleware(['auth', 'check.menu:24'])->name('product');

    Route::get('/pkg_format', function () {
        return view('master.pkgFormat');
    })->middleware(['auth', 'check.menu:27'])->name('pkg_format');

    Route::get('/rev-label', function () {
        return view('master.revision_label');
    })->middleware(['auth', 'check.menu:25'])->name('rev-label');

    Route::get('/stampFormat', function () {
        return view('master.stampFormat');
    })->middleware(['auth', 'check.menu:18'])->name('stampFormat');



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
    Route::get('/master/suppliers/{supplier}/links/data', [SuppliersController::class, 'linksData'])->name('suppliers.links.data');
    Route::post('/master/suppliers/{supplier}/links', [SuppliersController::class, 'storeLink'])->name('suppliers.links.store');
    Route::get('/master/suppliers/{supplier}/links/{link}', [SuppliersController::class, 'showLink'])->name('suppliers.links.show');
    Route::put('/master/suppliers/{supplier}/links/{link}', [SuppliersController::class, 'updateLink'])->name('suppliers.links.update');
    Route::delete('/master/suppliers/{supplier}/links/{link}', [SuppliersController::class, 'destroyLink'])->name('suppliers.links.destroy');
    #End region

    #Region Customers
    Route::resource('master/customers', CustomersController::class)->names('customers')->except(['create', 'edit']);
    Route::get('/customers/data', [CustomersController::class, 'data'])->name('customers.data');
    #End region

    #Region Model
    Route::resource('master/models', ModelsController::class)->names('models')->except(['create', 'edit']);
    Route::get('/models/data', [ModelsController::class, 'data'])->name('models.data');
    Route::get('/models/customers/select2', [ModelsController::class, 'customersSelect2'])->name('models.customers.select2');
    Route::get('/models/statuses/select2',  [ModelsController::class, 'statusesSelect2'])->name('models.statuses.select2');
    Route::get('/models/get-customers', [ModelsController::class, 'getCustomers'])->name('models.getCustomers');
    Route::get('/models/getStatus',     [ModelsController::class, 'getStatus'])->name('models.getStatus');
    #End region

    #Region Doc Type Group
    Route::resource('master/docTypeGroups', DocTypeGroupsController::class)->names('docTypeGroups')->except(['create', 'edit']);
    Route::get('/docTypeGroups/data', [DocTypeGroupsController::class, 'data'])->name('docTypeGroups.data');
    #End region

    #Region Doc Sub Category
    Route::resource('master/docTypeSubCategories', DocTypeSubCategoriesController::class)->names('docTypeSubCategories')->except(['create', 'edit']);
    Route::get('/docTypeSubCategories/data', [DocTypeSubCategoriesController::class, 'data'])->name('docTypeSubCategories.data');
    Route::get('/docTypeSubCategories/getDocTypeGroups', [DocTypeSubCategoriesController::class, 'getDocTypeGroups'])->name('docTypeSubCategories.getDocTypeGroups');
    Route::get('/docTypeSubCategories/select2/groups', [DocTypeSubCategoriesController::class, 'select2Groups'])->name('docTypeSubCategories.select2.groups');
    Route::get('/docTypeSubCategories/select2/customers', [DocTypeSubCategoriesController::class, 'select2Customers'])->name('docTypeSubCategories.select2.customers');
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

    Route::get('/partGroups/select2/customers', [PartGroupsController::class, 'select2Customers'])
        ->name('partGroups.select2.customers');
    Route::get('/partGroups/select2/models', [PartGroupsController::class, 'select2Models'])
        ->name('partGroups.select2.models');
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
    Route::post('/dashboard/detDataCardMonitoring', [DashboardController::class, 'detDataCardMonitoring'])->name('dashboard.detDataCardMonitoring');
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

    #Region products
    Route::resource('master/products', ProductsController::class)->names('products')->except(['create', 'edit']);
    Route::get('/products/data', [ProductsController::class, 'data'])->name('products.data');
    Route::get('/products/get-models', [ProductsController::class, 'getModels'])->name('products.getModels');
    Route::get('/products/get-customers', [ProductsController::class, 'getCustomers'])->name('products.getCustomers');
    #End region

    #Region upload
    Route::post('upload.getCustomerData', [DrawingUploadController::class, 'getCustomerData'])->name('upload.getCustomerData');
    Route::post('upload.getModelData', [DrawingUploadController::class, 'getModelData'])->name('upload.getModelData');
    Route::post('upload.getProductData', [DrawingUploadController::class, 'getProductData'])->name('upload.getProductData');
    Route::post('upload.getDocumentGroupData', [DrawingUploadController::class, 'getDocumentGroupData'])->name('upload.getDocumentGroupData');
    Route::post('upload.getSubCategoryData', [DrawingUploadController::class, 'getSubCategoryData'])->name('upload.getSubCategoryData');
    Route::post('upload.getPartGroupData', [DrawingUploadController::class, 'getPartGroupData'])->name('upload.getPartGroupData');
    Route::post('upload.getProjectStatusData', [DrawingUploadController::class, 'getProjectStatusData'])->name('upload.getProjectStatusData');

    Route::get('/upload/drawing/allowed-extensions', [DrawingUploadController::class, 'getPublicAllowedExtensions'])->name('upload.drawing.allowed-extensions');
    Route::get('/upload/drawing/get-revision-labels/{customerId}', [CustomerRevisionLabelController::class, 'getLabelsForCustomer'])->name('upload.drawing.get-labels');
    Route::post('/upload/drawing/check-revision-status', [DrawingUploadController::class, 'checkRevisionStatus'])->name('upload.drawing.check-status');
    Route::post('/upload/drawing/check-conflicts', [DrawingUploadController::class, 'checkConflicts'])
        ->name('upload.drawing.check-conflicts');

    Route::post('/upload/drawing/sync', [DrawingUploadController::class, 'syncLegacyData'])->name('upload.drawing.sync-legacy');
    Route::post('/upload/drawing/store', [DrawingUploadController::class, 'store'])->name('upload.drawing.store');
    Route::post('/upload/drawing/activity-logs', [DrawingUploadController::class, 'activityLogs'])->name('upload.drawing.activity-logs');
    Route::post('/upload/drawing/request-approval', [DrawingUploadController::class, 'requestApproval'])->name('upload.drawing.request-approval');
    Route::post('/upload/drawing/revise-confirm', [DrawingUploadController::class, 'reviseConfirmed'])->middleware(['auth'])
        ->name('upload.drawing.revise-confirm');

    Route::get('/files/kpi', [UploadController::class, 'getKpiStats'])->name('api.files.kpi-stats');
    Route::get('/files/list', [UploadController::class, 'listFiles'])->name('api.files.list');
    Route::get('/files/{id}', [UploadController::class, 'getPackageDetails'])->name('api.files.detail');
    #End region

    //Export
    Route::get('/export/kpi', [ExportController::class, 'kpi'])->middleware(['auth'])->name('api.export.kpi');

    Route::get('/export/filters', [ExportController::class, 'filters'])->middleware(['auth'])->name('api.export.filters');

    Route::get('/export/list', [ExportController::class, 'listExportableFiles'])->middleware(['auth'])->name('api.export.list');

    Route::get('/download/file/{file_id}', [ExportController::class, 'downloadFile'])->middleware('check.menu:4')->name('api.export.download-file');
    Route::get('/download/package/{revision_id}', [ExportController::class, 'downloadPackage'])->middleware('check.menu:4')->name('api.export.download-package');
    #End region

    #region Approval
    Route::get('/approval/summary', [ApprovalController::class, 'exportSummary'])->name('approvals.summary');
    Route::get('/approvals/filters', [ApprovalController::class, 'filters'])->name('approvals.filters');
    Route::get('/approvals/list', [ApprovalController::class, 'listApprovals'])->name('approvals.list');
    Route::get('/approval/{id}', [ApprovalController::class, 'showDetail'])->name('approval.detail');
    Route::post('/approvals/{id}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{id}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('/approvals/{id}/rollback', [ApprovalController::class, 'rollback'])->name('approvals.rollback');
    Route::get('/approvals/kpi', [ApprovalController::class, 'kpi'])->name('approvals.kpi');


    #endregion


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
    #End region

    #Region CustomerRev
    Route::resource('master/revisionLabels', CustomerRevisionLabelController::class)->parameters(['revisionLabels' => 'rev_label'])->names('rev-label')->except(['create', 'edit']);
    Route::get('rev-label/data', [CustomerRevisionLabelController::class, 'data'])->name('rev-label.data');
    Route::get('rev-label/dropdowns', [CustomerRevisionLabelController::class, 'dropdowns'])->name('rev-label.dropdowns');
    Route::post('rev-label/dropdowns', [CustomerRevisionLabelController::class, 'dropdowns']);
    #End region

    #Region PKG Format
    Route::get('pkg_format/data', [PackageFormatController::class, 'data'])->name('pkg_format.data');
    Route::resource('master/pkgFormat', PackageFormatController::class)->names('pkg_format')->except(['create', 'edit']);
    #End region

    #region File Preview
    Route::get('/preview/file/{id}', [FilePreviewController::class, 'show'])->middleware(['signed'])->name('preview.file');
    #end region

    #region Share
    Route::get('/share/get-roles', [ShareController::class, 'getRoles'])->name('share.getRoles');
    Route::get('/share/list', [ShareController::class, 'listPackage'])->name('share.list');
    Route::get('/share/filters', [ShareController::class, 'choiseFilter'])->name('share.filters');
    Route::post('/share/save', [ShareController::class, 'saveShare'])->name('share.save');



    #end region

});
