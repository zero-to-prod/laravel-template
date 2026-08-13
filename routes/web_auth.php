<?php

use App\Modules\Settings\Appearance\AppearanceController;
use App\Modules\Settings\Authentication\PasswordController;
use App\Modules\Settings\Profile\ProfileController;
use App\Modules\Verification\VerificationNotificationController;
use App\Modules\Verification\VerifyEmailController;
use App\Routes\Auth;
use App\Routes\MiddlewareTag;
use Illuminate\Support\Facades\Route;

Route::get(Auth::verificationVerify->value, VerifyEmailController::class)
    ->middleware('signed')
    ->name('verification.verify');
Route::post(Auth::verificationSend->value, VerificationNotificationController::class)
    ->middleware('throttle:6,1')
    ->name('verification.send');

Route::post(Auth::settingsProfile->value, ProfileController::class);
Route::post(Auth::settingsAuthentication->value, PasswordController::class);
Route::post(Auth::settingsAppearance->value, AppearanceController::class);

Route::middleware(MiddlewareTag::verified->value)->group(function () {
    Route::get(Auth::dashboard->value, fn () => response()->noContent());
});
