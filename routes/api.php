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

    Route::post('user-activation-sent-link', [UserController::class, 'user_activation_sent_link']);


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
            Route::post('menus', [FunctionController::class, 'menu_create']);

            // ************************* photos ***********************************
            Route::post('rest-photos', [FunctionController::class, 'rest_photo']);

            Route::post('review-update', [FunctionController::class, 'review_update']);
            Route::get('review-list/{rest_id}', [FunctionController::class, 'review_info']);

        });








        Route::prefix('admin')->group(function () {
            Route::post('restaurants', [AdminController::class, 'restaurant_create']);
            Route::get('/restaurant-for-admin',[AdminController::class,'restaurant_list_for_admin']);

        });
        Route::prefix('restaurant')->group(function () {
            Route::post('users', [UserController::class, 'user_create']);
            Route::post('restaurants-users', [UserController::class, 'restaurant_user_list']);
            Route::post('floors', [RestaurantController::class, 'floor_area_create']);
            Route::post('slot-create-update', [RestaurantController::class, 'slot_create']);
            Route::get('slot-delete/{rest_uuid}/{day}', [RestaurantController::class, 'slot_delete']);
            Route::get('slot-single-delete/{uuid}/', [RestaurantController::class, 'single_slot_delete']);
            Route::get('slot-info/{rest_uuid}', [RestaurantController::class, 'slot_info']);
            Route::post('tables', [RestaurantController::class, 'table_create']);
            Route::post('label-tags', [RestaurantController::class, 'label_tag_create']);
            Route::post('about-tags', [RestaurantController::class, 'about_tag_create']);
            Route::get('reservation-for-restaurant', [ReservationController::class, 'reservation_for_restaurant']);
        });


    });
});
