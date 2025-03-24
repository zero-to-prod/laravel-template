<?php

use App\Modules\Api\Logout\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post(api()->logout, LogoutController::class);
