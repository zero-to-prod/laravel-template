<?php

use App\Modules\Api\Authenticated\AuthenticatedController;
use App\Modules\Api\Login\ApiLoginController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::post(ApiRoute::login->value, ApiLoginController::class);
Route::get(ApiRoute::authenticated->value, AuthenticatedController::class);
