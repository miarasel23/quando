<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\FunctionController;





Route::prefix('v1/user/')->group(function () {

    Route::post('/login',[AdminController::class,'Login']);
    Route::post('guest-register', [UserController::class, 'guest_register']);
    Route::post('guest-login', [UserController::class, 'guest_login']);
    Route::get('/restaurant',[AdminController::class,'restaurant_list']);
    Route::get('/search-restaurant',[AdminController::class,'restaurant_search_list']);
    Route::get('/category',[AdminController::class,'category']);
    Route::get('restaurant-single-info/{uuid}', [AdminController::class, 'restaurant_single_info']);

    Route::post('review-create', [FunctionController::class, 'review_create']);


    Route::prefix('reservation')->group(function () {
        Route::get('reservation-time-hold', [ReservationController::class, 'time_hold']);
        Route::get('reservation-book', [ReservationController::class, 'reservation_book']);
        Route::get('reservation-removed', [ReservationController::class, 'reservation_removed']);
        Route::get('reservation-info/{uuid}', [ReservationController::class, 'reservation_info']);

    });

});




Route::group(['middleware' => ['auth:sanctum']], function () {



    Route::prefix('v1/secure')->group(function () {

        Route::post('logout', [AdminController::class, 'logout']);
        Route::post('guest-logout', [UserController::class, 'logout']);
        Route::prefix('guest-user')->group(function () {
            Route::get('profile/{uuid}', [UserController::class, 'profile']);
            Route::post('profile-update', [UserController::class, 'profile_update']);
        });


        Route::prefix('restaurant-function')->group(function () {
            Route::get('menu-catergory', [FunctionController::class, 'menu_catergory']);
            Route::post('menu-create', [FunctionController::class, 'menu_create']);
            Route::post('menu-update', [FunctionController::class, 'menu_update']);
            Route::get('menu-info/{uuid}', [FunctionController::class, 'menu_info']);
            Route::get('menu-delete/{uuid}', [FunctionController::class, 'menu_delete']);

            // ************************* photos ***********************************
            Route::post('rest-photo', [FunctionController::class, 'rest_photo']);
            Route::post('rest-photo-update', [FunctionController::class, 'rest_photo_update']);
            Route::get('rest-photo-info/{uuid}', [FunctionController::class, 'rest_photo_info']);
            Route::get('rest-photo-delete/{uuid}', [FunctionController::class, 'rest_photo_delete']);

            Route::post('review-update', [FunctionController::class, 'review_update']);
            Route::get('review-list/{rest_id}', [FunctionController::class, 'review_info']);

        });








        Route::prefix('admin')->group(function () {
            Route::post('restaurant-create', [AdminController::class, 'restaurant_create']);
            Route::post('restaurant-update', [AdminController::class, 'restaurant_update']);
            Route::get('restaurant-info/{uuid}', [AdminController::class, 'restaurant_info']);

            Route::get('restaurant-delete/{uuid}', [AdminController::class, 'restaurant_delete']);
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


            Route::post('label-tag-create', [RestaurantController::class, 'label_tag_create']);
            Route::post('label-tag-update', [RestaurantController::class, 'label_tag_update']);
            Route::get('label-tag-info/{rest_uuid}', [RestaurantController::class, 'label_tag_info']);
            Route::get('label-tag-delete/{uuid}', [RestaurantController::class, 'label_tag_delete']);

            Route::post('about-tag-create', [RestaurantController::class, 'about_tag_create']);
            Route::post('about-tag-update', [RestaurantController::class, 'about_tag_update']);
            Route::get('about-tag-info/{rest_uuid}', [RestaurantController::class, 'about_tag_info']);
            Route::get('about-tag-delete/{uuid}', [RestaurantController::class, 'about_tag_delete']);

        });


    });
});
