<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\Checkout\CheckoutController;
use App\Http\Controllers\Api\Permission\PermissionController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\RolePermission\RolePermissionController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);

    Route::post('/checkout', [CheckoutController::class, 'store']);
    Route::post('/transaction', [TransactionController::class, 'store']);
    
    // Permission routes
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    Route::post('/users/{user}/assign-role', [RolePermissionController::class, 'assignRole']);
    Route::post('/roles/{role}/assign-permission', [RolePermissionController::class, 'givePermissionToRole']);
    Route::get('/users/{user}/check-permission', [RolePermissionController::class, 'checkPermission']);

    // Route::group(['middleware' => ['role:admin']], function () {
    //     Route::post('/permissions', [RolePermissionController::class, 'createPermission']);
    //     Route::post('/roles', [RolePermissionController::class, 'createRole']);

    //     Route::post('/users/{user}/assign-role', [RolePermissionController::class, 'assignRole']);
    //     Route::post('/roles/{role}/assign-permission', [RolePermissionController::class, 'givePermissionToRole']);
    //     Route::get('/users/{user}/check-permission', [RolePermissionController::class, 'checkPermission']);

    //     // Role CRUD
    //     Route::put('/roles/{role}', [RolePermissionController::class, 'updateRole']);
    //     Route::delete('/roles/{role}', [RolePermissionController::class, 'deleteRole']);

    //     // Permission CRUD
    //     Route::put('/permissions/{permission}', [RolePermissionController::class, 'updatePermission']);
    //     Route::delete('/permissions/{permission}', [RolePermissionController::class, 'deletePermission']);
    //     Route::get('/permissions', [RolePermissionController::class, 'index']);
    // });

    Route::get('/products/scan/{barcode}', [ProductController::class, 'scanBarcode']);
    
    // Route::get('/test', function (Request $request) {
    //     return response()->json([
    //         'id' => encrypt(1),
    //     ]);
    // });
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);