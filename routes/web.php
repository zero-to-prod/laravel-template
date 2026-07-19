<?php

use App\Modules\Login\LoginController;
use App\Modules\Logout\LogoutController;
use App\Modules\Register\RegisterController;
use App\Routes\Web;
use Illuminate\Support\Facades\Route;

Route::post(Web::register->value, RegisterController::class)->middleware(['throttle:5,1']);
Route::post(Web::login->value, LoginController::class)->middleware(['throttle:5,1']);
Route::get(Web::logout->value, LogoutController::class);
