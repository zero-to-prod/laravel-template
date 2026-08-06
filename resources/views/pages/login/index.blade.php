<?php

use App\Modules\Login\LoginForm;
use App\Routes\Web;
use Illuminate\View\View;

use function Laravel\Folio\render;

render(function (View $view) {
    if (auth()->check()) {
        return redirect(Web::home->value);
    }

    return $view;
});
?>
<x-auth-card title="Login">
    <form class="space-y-4" method="POST" action="{{Web::login->value}}">
        @csrf
        <x-text-input :model="LoginForm::class" :name="LoginForm::email"/>
        <x-text-input :model="LoginForm::class" :name="LoginForm::password" autocomplete="current-password"/>
        <button class="btn btn-primary mt-4 w-full">Login</button>
        @if(isset($errors))
            <x-errors :$errors :take="1"/>
        @endif
    </form>
    <x-slot:footer>
        <a href="{{Web::register->value}}" class="link link-primary text-center p-3">Register</a>
    </x-slot:footer>
</x-auth-card>
