<?php

use App\Modules\Api\Logout\LogoutController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::post(ApiRoute::logout->value, LogoutController::class);
