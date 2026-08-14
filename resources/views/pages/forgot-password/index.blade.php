<?php

use App\Modules\PasswordReset\ForgotPasswordForm;
use App\Routes\Web;
use App\View\DataModels\AuthCard;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

use function Laravel\Folio\render;

Head::title('Forgot Password')
    ->description('Request a password reset link.')
    ->hiddenFromRobots();

render(function (View $view) {
    if (auth()->check()) {
        return redirect(Web::home->value);
    }

    return $view;
});
?>
<x-auth-card :authCard="[AuthCard::title => 'Reset your password']">
    <p class="mb-4 text-sm text-base-content/70">
        Enter your email and we will send you a secure link to choose a new password.
    </p>
    <form class="space-y-4" method="POST" action="{{Web::forgotPassword->value}}">
        @csrf
        <x-text-input :textInput="ForgotPasswordForm::textInput(ForgotPasswordForm::email)"/>
        <button class="btn btn-primary mt-4 w-full">Email reset link</button>
    </form>
    <x-slot:footer>
        <a href="{{Web::login->value}}" class="link link-primary text-center p-3">Back to login</a>
    </x-slot:footer>
</x-auth-card>
