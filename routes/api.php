<?php

use App\Modules\Api\Authenticated\AuthenticatedController;
use App\Modules\Api\Login\LoginController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::post(ApiRoute::login->value, LoginController::class);
Route::get(ApiRoute::authenticated->value, AuthenticatedController::class);
