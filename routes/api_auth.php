<?php

use App\Modules\Api\Public\User\Show\UserShowController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::get(ApiRoute::user->value, UserShowController::class);
