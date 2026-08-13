<?php

use App\Routes\Auth;
use App\Routes\Web;
use App\View\DataModels\AuthCard;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

use function Laravel\Folio\{name, render};

name('verification.notice');

Head::title('Verify Your Email')
    ->description('Confirm your email address to activate your account.')
    ->hiddenFromRobots();

render(function (View $view) {
    if (auth()->user()->hasVerifiedEmail()) {
        return redirect(Web::home->value);
    }

    return $view;
});
?>
<x-auth-card :authCard="[AuthCard::title => 'Verify Your Email']">
    <x-status-toast/>
    <p class="text-sm text-base-content/70">
        We've emailed you a verification link. Click it to activate your account.
    </p>
    <form method="POST" action="{{Auth::verificationSend->value}}" class="mt-4">
        @csrf
        <button class="btn btn-primary btn-sm w-full">Resend Verification Email</button>
    </form>
    <x-slot:footer>
        <a href="{{Web::logout->value}}" class="link link-primary text-center p-3">Logout</a>
    </x-slot:footer>
</x-auth-card>
