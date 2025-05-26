<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Category\CategoryController;
use App\Http\Controllers\Api\Checkout\CheckoutController;
use App\Http\Controllers\Api\Permission\PermissionController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Role\RoleController;
use App\Http\Controllers\Api\RolePermission\RolePermissionController;
use App\Http\Controllers\Api\StockMovement\StockMovementController;
use App\Http\Controllers\Api\Transaction\TransactionReportController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Transaction\TransactionController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    Route::post('register', [AuthController::class, 'register']);
    
    Route::apiResource('users', UserController::class);

    Route::apiResource('stockmovements', StockMovementController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    
    // Permission routes
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class);

    Route::post('users/{user}/assign-role', [RolePermissionController::class, 'assignRole']);
    Route::post('roles/{role}/assign-permission', [RolePermissionController::class, 'givePermissionToRole']);
    Route::get('users/{user}/check-permission', [RolePermissionController::class, 'checkPermission']);
    Route::get('users/{user}/roles-permissions', [RolePermissionController::class, 'getUserRolesAndPermissions']);

    Route::post('checkout', [CheckoutController::class, 'store']);
    Route::post('transaction', [TransactionController::class, 'store']);
    
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

    Route::get('products/scan/{barcode}', [ProductController::class, 'scanBarcode']);
    
    Route::get('stockmovements/stockin', [StockMovementController::class, 'stockin']);
    Route::get('stockmovements/stockout', [StockMovementController::class, 'stockout']);
    Route::get('export/stockin', [StockMovementController::class, 'exportStockIn'] );
    Route::get('export/stockout', [StockMovementController::class, 'exportStockOut'] );

    Route::get('reports/transactions', [TransactionReportController::class, 'index']);
    Route::get('reports/transactions/date', [TransactionReportController::class, 'reportByDate']);
    Route::get('reports/transactions/daily-summary', [TransactionReportController::class, 'dailySummary']);
    Route::get('export/report', [TransactionReportController::class, 'exportReport'] );
   
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Login gagal'], 401);
    }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
});


Route::post('/register', [AuthController::class, 'register']);