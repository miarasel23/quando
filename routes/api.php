<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserController;





Route::prefix('v1/user/')->group(function () {
    Route::post('/login',[AdminController::class,'Login']);
});




Route::group(['middleware' => ['auth:sanctum']], function () {




    Route::prefix('v1/secure')->group(function () {

        Route::post('logout', [AdminController::class, 'logout']);
        Route::prefix('admin')->group(function () {
            Route::post('restaurent-create', [AdminController::class, 'restaurent_create']);
            Route::post('restaurent-update', [AdminController::class, 'restaurent_update']);
        });


        Route::prefix('restaurent')->group(function () {
            Route::post('user-create', [UserController::class, 'user_create']);
            Route::post('user-update', [UserController::class, 'user_update']);
            Route::get('user-info/{uuid}', [UserController::class, 'user_info']);
        });


    });



});
