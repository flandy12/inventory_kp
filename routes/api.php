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
    
    /**
     * AUTH
     */
    Route::post('register', [AuthController::class, 'register']);

    /**
     * USER & PERMISSION MANAGEMENT
     */
    Route::prefix('users')->controller(RolePermissionController::class)->group(function () {
        Route::get('roles-permissions', 'getAllUsersWithRolesAndPermissions'); // All users
        Route::get('{user}/roles-permissions', 'getUserRolesAndPermissions');  // Specific user
        Route::post('{user}/assign-role', 'assignRole');
        Route::put('{user}/update-role', 'updateRole'); 
        Route::get('{user}/check-permission', 'checkPermission');
    });

    Route::prefix('roles')->controller(RolePermissionController::class)->group(function () {
        Route::post('{role}/assign-permission', 'givePermissionToRole');
    });

    Route::apiResource('roles', RoleController::class);
    // Mendapatkan permission role
    Route::get('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);

    // Menyimpan/assign permission ke role
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    
    Route::apiResource('permissions', PermissionController::class);

    /**
     * USERS
     */
    Route::apiResource('users', UserController::class);

    /**
     * PRODUCT & CATEGORY
     */
    Route::apiResource('products', ProductController::class);
    Route::get('products/scan/{id}', [ProductController::class, 'scanBarcode']);
    Route::get('products/{id}/barcode', [ProductController::class, 'getBarcodeGenerate']);
   
    Route::apiResource('categories', CategoryController::class);

    /**
     * STOCK MOVEMENT
     */
    Route::get('stockmovements/stockin', [StockMovementController::class, 'getStockIn']);
    Route::get('stockmovements/stockout', [StockMovementController::class, 'getStockOut']);
    Route::post('stockmovements/stockin', [StockMovementController::class, 'stockin']);

    Route::apiResource('stockmovements', StockMovementController::class);
  
    Route::get('export/stockin', [StockMovementController::class, 'exportStockIn']);
    Route::get('export/stockout', [StockMovementController::class, 'exportStockOut']);
    // Route::post('stockmovements/stockout', [StockMovementController::class, 'stockout']);


    /**
     * TRANSACTIONS
     */
    Route::apiResource('transactions', TransactionController::class);
    Route::post('transaction', [TransactionController::class, 'store']);
    Route::post('checkout', [CheckoutController::class, 'store']);
    Route::get('checkout/bestseller', [CheckoutController::class, 'getProductBestSeller']);
    Route::get('checkout', [CheckoutController::class, 'index']);



    /**
     * REPORT
     */
    Route::get('reports/transactions', [TransactionReportController::class, 'index']);
    Route::get('reports/transactions/date', [TransactionReportController::class, 'reportByDate']);
    Route::get('reports/transactions/daily-summary', [TransactionReportController::class, 'dailySummary']);
    Route::get('export/report', [TransactionReportController::class, 'exportReport']);

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
        'data' => [
            'id' => encrypt($user->id),
            'name' => $user->name,
            'email' => $user->email,
            'profile_url' => $user->profile_photo_url,
            'role' => $user->roles->pluck('name')->first(), // Ambil 1 role (jika hanya 1 yang digunakan)
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]
    ]);
});


Route::post('/register', [AuthController::class, 'register']);