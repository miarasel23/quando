<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;


Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::post('/user', function (Request $request) {

        $user= User::where('email', $request->email)->first();

        // $token = $user->createToken('login_access_tocken')->plainTextToken;

        return ['token' =>$user];

    });

});
