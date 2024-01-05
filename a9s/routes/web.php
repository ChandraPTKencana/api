<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix("stok/api")->group(function(){

    // Route::post('login', function () {
    //     return response()->json(["error"],400);
    // });

    Route::post('/login', [\App\Http\Controllers\User\UserAccount::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\User\UserAccount::class, 'logout']);
    // Route::put('/change_password', [\App\Http\Controllers\Internal\User\UserAccount::class, 'change_password']);
  
    Route::get('/check_user', [\App\Http\Controllers\User\UserAccount::class, 'checkUser']);
    Route::get('/profile', [\App\Http\Controllers\User\UserAccount::class, 'dataUser']);
    Route::put('/update_profile', [\App\Http\Controllers\User\UserAccount::class, 'updateUser']);

    Route::get('/units', [\App\Http\Controllers\Stok\UnitController::class, 'index']);
    Route::get('/unit', [\App\Http\Controllers\Stok\UnitController::class, 'show']);
    Route::post('/unit', [\App\Http\Controllers\Stok\UnitController::class, 'store']);
    Route::put('/unit', [\App\Http\Controllers\Stok\UnitController::class, 'update']);
    Route::delete('/unit', [\App\Http\Controllers\Stok\UnitController::class, 'delete']);

    Route::get('/warehouses', [\App\Http\Controllers\Stok\WarehouseController::class, 'index']);
    Route::get('/warehouse', [\App\Http\Controllers\Stok\WarehouseController::class, 'show']);
    Route::post('/warehouse', [\App\Http\Controllers\Stok\WarehouseController::class, 'store']);
    Route::put('/warehouse', [\App\Http\Controllers\Stok\WarehouseController::class, 'update']);
    Route::delete('/warehouse', [\App\Http\Controllers\Stok\WarehouseController::class, 'delete']);

    Route::get('/items', [\App\Http\Controllers\Stok\ItemController::class, 'index']);
    Route::get('/item', [\App\Http\Controllers\Stok\ItemController::class, 'show']);
    Route::post('/item', [\App\Http\Controllers\Stok\ItemController::class, 'store']);
    Route::put('/item', [\App\Http\Controllers\Stok\ItemController::class, 'update']);
    Route::delete('/item', [\App\Http\Controllers\Stok\ItemController::class, 'delete']);

    Route::get('/transactions', [\App\Http\Controllers\Stok\TransactionController::class, 'index']);
    Route::get('/transaction', [\App\Http\Controllers\Stok\TransactionController::class, 'show']);
    Route::post('/transaction', [\App\Http\Controllers\Stok\TransactionController::class, 'store']);
    Route::put('/transaction', [\App\Http\Controllers\Stok\TransactionController::class, 'update']);
    Route::delete('/transaction', [\App\Http\Controllers\Stok\TransactionController::class, 'delete']);
    
    Route::get('/request_transactions', [\App\Http\Controllers\Stok\TransactionController::class, 'request_transactions']);
    Route::post('/request_transaction_confirm', [\App\Http\Controllers\Stok\TransactionController::class, 'request_transaction_confirm']);

    Route::get('/summary_transactions', [\App\Http\Controllers\Stok\TransactionController::class, 'summary_transactions']);

    Route::get('/hrm_revisi_lokasis', [\App\Http\Controllers\HrmRevisiLokasiController::class, 'index']);
    
    // Route::get('/users', [\App\Http\Controllers\Internal\User\UserController::class, 'index']);
    // Route::get('/user', [\App\Http\Controllers\Internal\User\UserController::class, 'show']);
    // Route::post('/user', [\App\Http\Controllers\Internal\User\UserController::class, 'store']);
    // Route::put('/user', [\App\Http\Controllers\Internal\User\UserController::class, 'update']);
    // Route::delete('/user', [\App\Http\Controllers\Internal\User\UserController::class, 'delete']);
  
    // Route::get('/action_permissions', [\App\Http\Controllers\Internal\User\UserPermissionController::class, 'getActionPermissions']);
    // Route::get('/data_permissions', [\App\Http\Controllers\Internal\User\UserPermissionController::class, 'getDataPermissions']);
    // Route::get('/user/permissions', [\App\Http\Controllers\Internal\User\UserPermissionController::class, 'show']);
    // Route::put('/user/permissions', [\App\Http\Controllers\Internal\User\UserPermissionController::class, 'update']);
  
    // Route::get('/institutes', [\App\Http\Controllers\Internal\InstituteController::class, 'index']);
    // Route::get('/institute', [\App\Http\Controllers\Internal\InstituteController::class, 'show']);
    // Route::post('/institute', [\App\Http\Controllers\Internal\InstituteController::class, 'store']);
    // Route::put('/institute', [\App\Http\Controllers\Internal\InstituteController::class, 'update']);
    // Route::delete('/institute', [\App\Http\Controllers\Internal\InstituteController::class, 'delete']);
  
    // Route::get('/members', [\App\Http\Controllers\Internal\MemberController::class, 'index']);
    // Route::get('/member', [\App\Http\Controllers\Internal\MemberController::class, 'show']);
    // Route::post('/member', [\App\Http\Controllers\Internal\MemberController::class, 'store']);
    // Route::put('/member', [\App\Http\Controllers\Internal\MemberController::class, 'update']);
    // Route::delete('/member', [\App\Http\Controllers\Internal\MemberController::class, 'delete']);
  
});