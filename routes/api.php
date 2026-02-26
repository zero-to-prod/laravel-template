<?php

use App\Modules\Api\Authenticated\AuthenticatedController;
use App\Modules\Api\Discovery\DiscoveryController;
use App\Modules\Api\Login\ApiLoginController;
use Illuminate\Support\Facades\Route;

Route::get(api()->discovery, DiscoveryController::class);
Route::post(api()->login, ApiLoginController::class);
Route::get(api()->authenticated, AuthenticatedController::class);
