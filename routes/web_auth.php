<?php

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

Route::middleware(MiddlewareTag::verified->value)->group(function () {
    Route::get(Web::dashboard->value, fn () => response());
});
