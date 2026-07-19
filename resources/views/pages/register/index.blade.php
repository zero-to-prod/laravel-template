@php
    use App\DataModels\User;
    use App\Routes\Web;
@endphp

<x-auth-card title="Register">
    <form class="space-y-4" method="POST" action="{{Web::register->value}}">
        @csrf
        <x-text-input legend="Full Name" :name="User::name" :value="old(User::name)" icon="user"
                      placeholder="First and Last Name" required/>
        <x-text-input legend="Email" :name="User::email" :value="old(User::email)" type="email" icon="email"
                      :required="User::isRequired(User::email)" placeholder="Email"/>
        <x-text-input legend="Password" :name="User::password" type="password" icon="key"
                      :required="User::isRequired(User::password)" placeholder="Password"/>
        <x-text-input legend="Password Confirmation" :name="User::password_confirmation" type="password" icon="key"
                      placeholder="Password Confirmation"/>
        <button class="btn btn-primary mt-4 w-full">Register</button>
    </form>
    <x-slot:footer>
        @guest
            <a href="{{Web::login->value}}" class="link link-primary text-center p-3">Login</a>
        @endguest
    </x-slot:footer>
</x-auth-card>
