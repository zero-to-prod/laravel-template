<?php

use Illuminate\View\View;

use function Laravel\Folio\render;

render(function (View $view) {
    if (auth()->check()) {
        return redirect(web()->home);
    }

    return $view;
});
?>
<x-main>
    <div class="card card-compact sm:m-auto sm:mt-24 sm:max-w-sm">
        <div class="card-body">
            <h1 class="card-title">Login</h1>
            <form class="space-y-2" method="POST" action="{{web()->login}}">
                @csrf
                <label class="w-full form-control">
                    <div class="label">
                        <span class="label-text">Email</span>
                    </div>
                    <label class="flex items-center gap-2 input input-bordered bg-base-200">
                        <x-svg name="email" classname="h-4 w-4 opacity-70"/>
                        <input type="text" name="email" class="grow" placeholder="Email" required/>
                    </label>
                </label>
                <label class="w-full form-control">
                    <div class="label">
                        <span class="label-text">Password</span>
                    </div>
                    <label class="flex items-center gap-2 input input-bordered bg-base-200">
                        <x-svg name="key" classname="h-4 w-4 opacity-70"/>
                        <input type="password" name="password" class="grow" placeholder="Password" required/>
                    </label>
                </label>
                <div>
                    <button class="mt-6 w-full btn btn-primary">Login</button>
                </div>
                @if(isset($errors))
                    <x-errors :$errors :take="1"/>
                @endif
            </form>
            <div class="divider">or</div>
            <a href="{{web()->register}}" class="link link-primary text-center p-3">Register</a>
        </div>
    </div>
</x-main>
