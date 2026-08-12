<?php

use App\Modules\Login\LoginForm;
use App\Routes\Web;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

use function Laravel\Folio\render;

Head::title('Login')
    ->description('Sign in to your account.')
    ->hiddenFromRobots();

render(function (View $view) {
    if (auth()->check()) {
        return redirect(Web::home->value);
    }

    return $view;
});
?>
<x-main>
    <div class="card card-compact sm:m-auto sm:mt-24 sm:max-w-sm">
        <div class="card-body">
            <h1 class="card-title">Login</h1>
            <form class="space-y-2" method="POST" action="{{Web::login->value}}">
                @csrf
                <x-text-input :textInput="LoginForm::textInput(LoginForm::email)"/>
                <x-text-input :textInput="LoginForm::textInput(LoginForm::password)"/>
                <div>
                    <button class="mt-6 w-full btn btn-primary">Login</button>
                </div>
            </form>
            <div class="divider">or</div>
            <a href="{{Web::register->value}}" class="link link-primary text-center p-3">Register</a>
        </div>
    </div>
</x-main>
