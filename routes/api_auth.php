<?php

use App\Modules\Api\Logout\LogoutController;
use App\Modules\Api\User\Show\UserShowController;
use App\Modules\Api\User\Update\UserUpdateController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::post(ApiRoute::logout->value, LogoutController::class);
Route::get(ApiRoute::user->value, UserShowController::class);
Route::patch(ApiRoute::user->value, UserUpdateController::class);
