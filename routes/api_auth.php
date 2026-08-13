<?php

use App\Modules\Api\User\Show\UserShowController;
use App\Modules\Api\User\Token\Destroy\UserTokenDestroyController;
use App\Modules\Api\User\Token\Index\UserTokenIndexController;
use App\Modules\Api\User\Token\Show\UserTokenShowController;
use App\Modules\Api\User\Token\Store\UserTokenStoreController;
use App\Modules\Api\User\Update\UserUpdateController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::get(ApiRoute::user->value, UserShowController::class);
Route::patch(ApiRoute::user->value, UserUpdateController::class);
Route::get(ApiRoute::user_tokens->value, UserTokenIndexController::class);
Route::post(ApiRoute::user_tokens->value, UserTokenStoreController::class);
Route::get(ApiRoute::user_token->value, UserTokenShowController::class);
Route::delete(ApiRoute::user_token->value, UserTokenDestroyController::class);
