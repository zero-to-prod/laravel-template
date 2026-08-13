@php
    use App\Modules\Register\RegisterForm;use App\Routes\Web;use App\View\DataModels\AuthCard;use Laravel\Head\Facades\Head;

    Head::title('Register')->description('Create your account.');
@endphp

<x-auth-card :authCard="[AuthCard::title => 'Register']">
    <form class="space-y-4" method="POST" action="{{Web::register->value}}">
        @csrf
        <x-text-input :textInput="RegisterForm::textInput(RegisterForm::name)"/>
        <x-text-input :textInput="RegisterForm::textInput(RegisterForm::email)"/>
        <x-text-input :textInput="RegisterForm::textInput(RegisterForm::password)"/>
        <x-text-input :textInput="RegisterForm::textInput(RegisterForm::password_confirmation)"/>
        <button class="btn btn-primary mt-4 w-full">Register</button>
    </form>
    <x-slot:footer>
        @guest
            <a href="{{Web::login->value}}" class="link link-primary text-center p-3">Login</a>
        @endguest
    </x-slot:footer>
</x-auth-card>
