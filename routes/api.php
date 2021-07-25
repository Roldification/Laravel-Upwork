<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/create-user', [UserController::class, 'store']);

Route::middleware('auth:api')->post('/send-invites', [UserController::class, 'sendInvites']);

Route::post('/update-user-info', [UserController::class, 'update']);

Route::post('/confirm-registration', [UserController::class, 'confirmRegistration']);

Route::post('/login-user', [UserController::class, 'login']);

Route::middleware('auth:api')->post('/update-profile', [UserController::class, 'updateProfile']);