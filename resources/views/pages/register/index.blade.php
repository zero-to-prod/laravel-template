@php
    use App\DataModels\User;
    use App\Routes\Web;
@endphp

<x-auth-card title="Register">
    <form class="space-y-4" method="POST" action="{{Web::register->value}}">
        @csrf
        <x-text-input :model="User::class" :name="User::name"/>
        <x-text-input :model="User::class" :name="User::email"/>
        <x-text-input :model="User::class" :name="User::password"/>
        <x-text-input :model="User::class" :name="User::password_confirmation"/>
        <button class="btn btn-primary mt-4 w-full">Register</button>
    </form>
    <x-slot:footer>
        @guest
            <a href="{{Web::login->value}}" class="link link-primary text-center p-3">Login</a>
        @endguest
    </x-slot:footer>
</x-auth-card>
