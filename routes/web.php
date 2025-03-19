<?php

use App\Modules\Login\LoginController;
use App\Modules\Logout\LogoutController;
use App\Modules\Register\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get(web()->home, fn() => view('home'));
Route::post(web()->register, RegisterController::class)
    ->middleware(['throttle:5,1']);
Route::post(web()->login, LoginController::class)
    ->middleware(['throttle:5,1']);
Route::get(web()->logout, LogoutController::class);
