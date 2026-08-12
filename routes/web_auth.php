<?php

use App\Modules\Settings\Appearance\AppearanceController;
use App\Modules\Settings\Authentication\PasswordController;
use App\Modules\Settings\Profile\ProfileController;
use App\Modules\Verification\VerificationNotificationController;
use App\Modules\Verification\VerifyEmailController;
use App\Routes\MiddlewareTag;
use App\Routes\Web;
use Illuminate\Support\Facades\Route;

Route::get(Web::verificationVerify->value, VerifyEmailController::class)
    ->middleware('signed')
    ->name('verification.verify');
Route::post(Web::verificationSend->value, VerificationNotificationController::class)
    ->middleware('throttle:6,1')
    ->name('verification.send');

Route::post(Web::settingsProfile->value, ProfileController::class);
Route::post(Web::settingsAuthentication->value, PasswordController::class);
Route::post(Web::settingsAppearance->value, AppearanceController::class);

Route::middleware(MiddlewareTag::verified->value)->group(function () {
    Route::get(Web::dashboard->value, fn () => response()->noContent());
});
