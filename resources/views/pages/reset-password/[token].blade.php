<?php

use App\Modules\PasswordReset\ResetPasswordForm;
use App\Routes\Web;
use App\View\DataModels\AuthCard;
use App\View\DataModels\TextInput;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

use function Laravel\Folio\name;
use function Laravel\Folio\render;

name('password.reset');

Head::title('Reset Password')
    ->description('Choose a new password for your account.')
    ->hiddenFromRobots();

render(function (View $view) {
    if (auth()->check()) {
        return redirect(Web::home->value);
    }

    return $view;
});
?>
<x-auth-card :authCard="[AuthCard::title => 'Choose a new password']">
    <form class="space-y-4" method="POST" action="{{Web::resetPassword->url([ResetPasswordForm::token => $token])}}">
        @csrf
        <x-text-input :textInput="[
            ...ResetPasswordForm::textInput(ResetPasswordForm::email),
            TextInput::value => request()->string(ResetPasswordForm::email)->toString(),
        ]"/>
        <x-text-input :textInput="ResetPasswordForm::textInput(ResetPasswordForm::password)"/>
        <x-text-input :textInput="ResetPasswordForm::textInput(ResetPasswordForm::password_confirmation)"/>
        <button class="btn btn-primary mt-4 w-full">Reset password</button>
    </form>
    <x-slot:footer>
        <a href="{{Web::login->value}}" class="link link-primary text-center p-3">Back to login</a>
    </x-slot:footer>
</x-auth-card>
