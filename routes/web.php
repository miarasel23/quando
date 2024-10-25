<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/activation-link', [App\Http\Controllers\DashboardController::class, 'activation_link']);

Auth::routes();






// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/email-template', function () {
    return view('reservation.confirmation_email');
});
