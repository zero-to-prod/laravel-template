<?php

use App\Helpers\SvgName;
use App\Routes\Web;
use App\View\DataModels\AuthCard;
use App\View\DataModels\Svg;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

use function Laravel\Folio\render;

Head::title('Check Your Email')
    ->description('A password reset link has been requested.')
    ->hiddenFromRobots();

render(function (View $view) {
    if (auth()->check()) {
        return redirect(Web::home->value);
    }

    return $view;
});
?>
<x-auth-card :authCard="[AuthCard::title => 'Check your email']">
    <div class="space-y-5 text-center">
        <div class="mx-auto grid size-16 place-items-center rounded-full bg-primary/10 text-primary" aria-hidden="true">
            <x-svg :svg="[Svg::name => SvgName::email, Svg::classname => 'size-8']"/>
        </div>
        <div class="space-y-2">
            <p class="text-base font-medium">Your reset link is on its way.</p>
            <p class="text-sm leading-6 text-base-content/70">
                If an account exists for that email, you will receive a password reset link shortly.
                Check your spam folder if it does not appear in your inbox.
            </p>
        </div>
        <a href="{{Web::login->value}}" class="btn btn-primary w-full">Back to login</a>
    </div>
    <x-slot:footer>
        <a href="{{Web::forgotPassword->value}}" class="link link-primary text-center p-3">Send another link</a>
    </x-slot:footer>
</x-auth-card>
