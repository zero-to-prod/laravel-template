<?php

use App\Modules\Api\Admin\User\Delete\AdminUserDeleteController;
use App\Modules\Api\Admin\User\Index\AdminUserIndexController;
use App\Modules\Api\Admin\User\Show\AdminUserShowController;
use App\Modules\Api\Admin\User\Store\AdminUserStoreController;
use App\Modules\Api\Admin\User\Update\AdminUserUpdateController;
use App\Routes\Admin;
use Illuminate\Support\Facades\Route;

Route::get(Admin::api_users->value, AdminUserIndexController::class);
Route::post(Admin::api_users->value, AdminUserStoreController::class);
Route::get(Admin::api_user->value, AdminUserShowController::class);
Route::patch(Admin::api_user->value, AdminUserUpdateController::class);
Route::delete(Admin::api_user->value, AdminUserDeleteController::class);
