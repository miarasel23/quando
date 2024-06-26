<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;





Route::prefix('v1/user/')->group(function () {
    Route::post('/login',[AdminController::class,'Login']);
});




Route::group(['middleware' => ['auth:sanctum']], function () {




    Route::prefix('v1/secure')->group(function () {

        Route::post('logout', [AdminController::class, 'logout']);
        Route::prefix('admin')->group(function () {
            Route::post('restaurant-create', [AdminController::class, 'restaurent_create']);
            Route::post('restaurant-update', [AdminController::class, 'restaurent_update']);
        });


        Route::prefix('restaurant')->group(function () {
            Route::post('user-create', [UserController::class, 'user_create']);
            Route::post('user-update', [UserController::class, 'user_update']);
            Route::get('user-info/{uuid}', [UserController::class, 'user_info']);

            Route::post('floor-area-create', [RestaurantController::class, 'floor_area_create']);
            Route::post('floor-area-update', [RestaurantController::class, 'floor_area_update']);
            Route::get('floor-area-info/{rest_uuid}', [RestaurantController::class, 'floor_area_info']);
            Route::get('floor-area-delete/{uuid}', [RestaurantController::class, 'floor_area_delete']);


            Route::post('slot-create-update', [RestaurantController::class, 'slot_create']);
            Route::get('slot-info/{rest_uuid}', [RestaurantController::class, 'slot_info']);



            Route::post('table-create', [RestaurentController::class, 'table_create']);
            Route::post('table-update', [RestaurentController::class, 'table_update']);
            Route::get('table-info/{rest_uuid}', [RestaurentController::class, 'table_info']);
            Route::get('table-delete/{uuid}', [RestaurentController::class, 'table_delete']);
        });


    });



});
