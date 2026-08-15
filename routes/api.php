<?php

use App\Modules\Api\Public\Authenticated\AuthenticatedController;
use App\Modules\Api\Public\Readme\ReadmeController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::get(ApiRoute::authenticated->value, AuthenticatedController::class);
Route::get(ApiRoute::readme->value, ReadmeController::class);
