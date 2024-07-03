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
    Route::get('/restaurant',[AdminController::class,'restaurent_list']);
    Route::get('/search-restaurant',[AdminController::class,'restaurent_search_list']);
    Route::get('/category',[AdminController::class,'category']);
});




Route::group(['middleware' => ['auth:sanctum']], function () {




    Route::prefix('v1/secure')->group(function () {

        Route::post('logout', [AdminController::class, 'logout']);
        Route::prefix('admin')->group(function () {
            Route::post('restaurant-create', [AdminController::class, 'restaurent_create']);
            Route::post('restaurant-update', [AdminController::class, 'restaurent_update']);
            Route::get('restaurant-info/{uuid}', [AdminController::class, 'restaurent_info']);
            Route::get('restaurant-delete/{uuid}', [AdminController::class, 'restaurent_delete']);
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



            Route::post('table-create', [RestaurantController::class, 'table_create']);
            Route::post('table-update', [RestaurantController::class, 'table_update']);
            Route::get('table-info/{rest_uuid}', [RestaurantController::class, 'table_info']);
            Route::get('table-delete/{uuid}', [RestaurantController::class, 'table_delete']);


            Route::post('label-taq-create', [RestaurantController::class, 'label_taq_create']);
            Route::post('label-taq-update', [RestaurantController::class, 'label_taq_update']);
            Route::get('label-taq-info/{rest_uuid}', [RestaurantController::class, 'label_taq_info']);
            Route::get('label-taq-delete/{uuid}', [RestaurantController::class, 'label_taq_delete']);

            Route::post('about-taq-create', [RestaurantController::class, 'about_taq_create']);
            Route::post('about-taq-update', [RestaurantController::class, 'about_taq_update']);
            Route::get('about-taq-info/{rest_uuid}', [RestaurantController::class, 'about_taq_info']);
            Route::get('about-taq-delete/{uuid}', [RestaurantController::class, 'about_taq_delete']);

        });


    });
});
