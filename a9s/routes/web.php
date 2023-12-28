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