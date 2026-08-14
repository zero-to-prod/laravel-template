<?php

use App\Modules\Admin\Users\Delete\UserDeleteController;
use App\Modules\Admin\Users\Providers\ProviderDeleteController;
use App\Modules\Admin\Users\Update\UsersUpdateController;
use App\Routes\Admin;
use Illuminate\Support\Facades\Route;

Route::post(Admin::user->value, UsersUpdateController::class);
Route::delete(Admin::user->value, UserDeleteController::class);
Route::delete(Admin::userProvider->value, ProviderDeleteController::class);
