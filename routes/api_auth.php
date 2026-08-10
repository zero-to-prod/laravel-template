<?php

use App\Modules\Api\Logout\LogoutController;
use App\Modules\Api\User\ApiUserController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::post(ApiRoute::logout->value, LogoutController::class);
Route::get(ApiRoute::user->value, ApiUserController::class);
