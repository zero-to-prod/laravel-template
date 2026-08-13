<?php

use App\Modules\Admin\Users\Update\UsersUpdateController;
use App\Routes\Admin;
use Illuminate\Support\Facades\Route;

Route::post(Admin::user->value, UsersUpdateController::class);
