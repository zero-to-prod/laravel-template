<?php

use App\Modules\Api\Logout\LogoutController;
use App\Modules\Api\User\Show\UserShowController;
use App\Modules\Api\User\Token\Destroy\UserTokenDestroyController;
use App\Modules\Api\User\Token\Index\UserTokenIndexController;
use App\Modules\Api\User\Token\Show\UserTokenShowController;
use App\Modules\Api\User\Token\Store\UserTokenStoreController;
use App\Modules\Api\User\Update\UserUpdateController;
use App\Routes\ApiRoute;
use Illuminate\Support\Facades\Route;

Route::post(ApiRoute::logout->value, LogoutController::class);
Route::get(ApiRoute::user->value, UserShowController::class);
Route::patch(ApiRoute::user->value, UserUpdateController::class);

// The apiResource set for the caller's own tokens, minus `update`: a token's
// name and abilities are what its secret was issued against, so it is reissued
// rather than edited.
Route::get(ApiRoute::user_tokens->value, UserTokenIndexController::class);
Route::post(ApiRoute::user_tokens->value, UserTokenStoreController::class);
Route::get(ApiRoute::user_token->value, UserTokenShowController::class);
Route::delete(ApiRoute::user_token->value, UserTokenDestroyController::class);
