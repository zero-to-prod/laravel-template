<?php

use App\Modules\Settings\Authentication\PasswordForm;
use App\Routes\Auth;
use App\View\DataModels\SettingsCard;
use Laravel\Head\Facades\Head;

Head::title('Authentication')
    ->description('Confirm your current password to choose a new one.')
    ->hiddenFromRobots();
?>
<x-settings-card :settingsCard="[SettingsCard::title => 'Authentication']">
    <x-status-toast/>
    <p class="text-sm text-base-content/70">
        Confirm your current password to choose a new one.
    </p>
    <form class="mt-2 space-y-4" method="POST" action="{{Auth::settingsAuthentication->value}}">
        @csrf
        <input type="text" name="username" autocomplete="username" value="{{auth()->user()?->email}}" readonly hidden>
        <x-text-input :textInput="PasswordForm::textInput(PasswordForm::current_password)"/>
        <x-text-input :textInput="PasswordForm::textInput(PasswordForm::password)"/>
        <x-text-input :textInput="PasswordForm::textInput(PasswordForm::password_confirmation)"/>
        <button class="btn btn-primary">Update Password</button>
    </form>
</x-settings-card>
