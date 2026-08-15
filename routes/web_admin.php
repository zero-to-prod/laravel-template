<?php

use App\Modules\Admin\Content\ContentUpdateController;
use App\Modules\Admin\Sessions\SessionDeleteController;
use App\Modules\Admin\Sessions\SessionsClearController;
use App\Modules\Admin\Users\Delete\UserDeleteController;
use App\Modules\Admin\Users\Providers\ProviderDeleteController;
use App\Modules\Admin\Users\Update\UsersUpdateController;
use App\Routes\Admin;
use Illuminate\Support\Facades\Route;

Route::post(Admin::content->value, ContentUpdateController::class);
Route::delete(Admin::sessions->value, SessionsClearController::class);
Route::delete(Admin::session->value, SessionDeleteController::class);
Route::post(Admin::user->value, UsersUpdateController::class);
Route::delete(Admin::user->value, UserDeleteController::class);
Route::delete(Admin::userProvider->value, ProviderDeleteController::class);
