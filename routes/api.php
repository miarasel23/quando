<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Http\Controllers\Admin\AdminController;




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


    });



});
