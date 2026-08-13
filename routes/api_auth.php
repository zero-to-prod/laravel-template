<?php

use App\Modules\Api\Cache\Destroy\CacheDestroyController;
use App\Modules\Api\Cache\Index\CacheIndexController;
use App\Modules\Api\Cache\Show\CacheShowController;
use App\Modules\Api\Cache\Store\CacheStoreController;
use App\Modules\Api\CacheLocks\Destroy\CacheLocksDestroyController;
use App\Modules\Api\CacheLocks\Index\CacheLocksIndexController;
use App\Modules\Api\CacheLocks\Show\CacheLocksShowController;
use App\Modules\Api\CacheLocks\Store\CacheLocksStoreController;
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
Route::get(ApiRoute::user_tokens->value, UserTokenIndexController::class);
Route::post(ApiRoute::user_tokens->value, UserTokenStoreController::class);
Route::get(ApiRoute::user_token->value, UserTokenShowController::class);
Route::delete(ApiRoute::user_token->value, UserTokenDestroyController::class);
Route::get(ApiRoute::cache_key->value, CacheShowController::class);
Route::get(ApiRoute::cache->value, CacheIndexController::class);
Route::post(ApiRoute::cache->value, CacheStoreController::class);
Route::delete(ApiRoute::cache_key->value, CacheDestroyController::class);
Route::get(ApiRoute::cache_locks_key->value, CacheLocksShowController::class);
Route::get(ApiRoute::cache_locks->value, CacheLocksIndexController::class);
Route::post(ApiRoute::cache_locks->value, CacheLocksStoreController::class);
Route::delete(ApiRoute::cache_locks_key->value, CacheLocksDestroyController::class);
