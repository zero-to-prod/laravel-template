<?php

use App\Modules\PasswordConfirmation\PasswordConfirmationForm;
use App\Routes\Auth;
use App\View\DataModels\AuthCard;
use Laravel\Head\Facades\Head;

use function Laravel\Folio\name;

name('password.confirm');

Head::title('Confirm Password')
    ->description('Confirm your password before continuing.')
    ->hiddenFromRobots();
?>
<x-auth-card :authCard="[AuthCard::title => 'Confirm your password']">
    <p class="mb-4 text-sm leading-6 text-base-content/70">
        This area contains sensitive account information. Enter your password to continue securely.
    </p>
    <form class="space-y-4" method="POST" action="{{Auth::confirmPassword->value}}">
        @csrf
        <x-text-input :textInput="PasswordConfirmationForm::textInput(PasswordConfirmationForm::password)"/>
        <button class="btn btn-primary mt-4 w-full">Confirm password</button>
    </form>
</x-auth-card>
